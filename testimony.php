<?php
session_start();
date_default_timezone_set('Asia/Manila');
require("0conn.php");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['username'])) {
        header("Location: 3login.php");
        exit();
    }

    $username = $_SESSION['username'];

    // Handle adding a new testimonial
    if (isset($_POST['testimonial_text'])) {
        $testimonial_text = $_POST['testimonial_text'];
        $testimonial_text = implode(' ', array_slice(str_word_count($testimonial_text, 2), 0, 100));

        try {
            $stmt = $conn->prepare("INSERT INTO testimonies (username, testimonial_text, date_posted) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $username, $testimonial_text);
            $stmt->execute();
            $stmt->close();
            header("Location: testimony.php");
            exit();
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    // Handle deleting a testimonial
    if (isset($_POST['delete_testimonial_id'])) {
        $testimonial_id = $_POST['delete_testimonial_id'];

        try {
            // Check if the testimonial belongs to the logged-in user
            $check_stmt = $conn->prepare("SELECT * FROM testimonies WHERE testimony_id = ? AND username = ?");
            $check_stmt->bind_param("is", $testimonial_id, $username);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                // Delete the testimonial
                $delete_stmt = $conn->prepare("DELETE FROM testimonies WHERE testimony_id = ?");
                $delete_stmt->bind_param("i", $testimonial_id);
                $delete_stmt->execute();
                $delete_stmt->close();
            }

            $check_stmt->close();
            header("Location: testimony.php");
            exit();
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }
}

// Fetch recent testimonies
$fetchRecentTestimoniesStmt = $conn->prepare("SELECT * FROM testimonies ORDER BY date_posted DESC LIMIT 5");
$fetchRecentTestimoniesStmt->execute();
$recentTestimonies = $fetchRecentTestimoniesStmt->get_result()->fetch_all(MYSQLI_ASSOC);

function getTimeElapsedString($datetime)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d == 0) {
        if ($diff->h == 0) {
            if ($diff->i == 0) {
                return 'Now';
            } else {
                return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
            }
        } else {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        }
    } elseif ($diff->d == 1) {
        return '1 day ago';
    } elseif ($diff->d < 7) {
        return $diff->d . ' days ago';
    } else {
        return $ago->format('F j, Y');
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <title>eSangkap</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
        }

        .logo-container {
            padding: 7px;
            position: fixed;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .logo-container img {
            height: 60px;
            width: auto;
            margin-right: 10px;
            border-radius: 50%;
        }

        .logo-title {
            color: #f04e23;

        }

        .sidebar {
            background-color: #f04e23;
            height: 100%;
            width: 250px;
            position: fixed;
            top: 70px;
            left: 0;
            overflow-x: hidden;
            padding-top: 20px;
            display: flex;
            flex-direction: column;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px 25px;
            text-decoration: none;
            font-size: 15px;
            text-align: left;
            display: flex;
            align-items: center;
        }

        .sidebar a i {
            margin-right: 15px;
        }

        .sidebar a.active {
            background-color: #ffcccb;
            color: darkred;
        }

        .sidebar a:hover {
            background-color: white;
            color: darkred;
        }

        .container {
            margin-left: 250px;
            margin-top: 120px;
            width: calc(100% - 250px);
            padding: 20px;
            box-sizing: border-box;
            align-items: center;
        }


        form {
            max-width: 850px;
            margin: auto;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        textarea {
            width: 100%;
            padding: 15px;
            margin: 10px 0 20px;
            border: none;
            border-radius: 30px;
            background-color: #f4f4f4;
            resize: none;
            font-size: 1rem;
            outline: none;
        }

        input[type="submit"] {
            width: 100%;
            padding: 15px;
            background-color: darkred;
            color: white;
            border: none;
            border-radius: 25px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
        }

        h3 {
            margin-left: 255px;
            font-size: 23px;
            margin-top: 50px;
        }


        .testimonial-container {
            border:black 19px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 2em;
            margin-bottom: 3em;
        }

        .testimonial-card {
            border:black 19px;
            position: relative;
            width: 60%;
            padding: 20px;
            margin-bottom: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 6px 9px rgba(0, 0, 0, 0.1);
        }

        .testimonial-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .testimonial-card p {
            margin: 0 0 10px;
            font-size: 1rem;
        }

        .testimonial-card strong {
            color: #f04e23;
        }

        .delete-form {
            background: none;
            border: none;
            padding: 0;
            margin: 0;
        }

        .delete-icon {
            color: gray;
            font-size: 16px;
            background: none;
            border: none;
            cursor: pointer;
        }

        .delete-icon:hover {
            color: darkred;
        }
    </style>
</head>

<body>
    <div class="logo-container">
        <img src="logo.jpg" alt="Logo">
        <h2>
            <div class="logo-title">eSangkap</div>
        </h2>
    </div>
    <div class="sidebar">
        <a href="9customer.php"><i class="fa fa-fw fa-home"></i> Home</a>
        <a href="favoritescreen.php"><i class="fa-solid fas fa-heart"></i>Favorites</a>
        <a href="view_categories.php"><i class="fas fa-list"></i> Categories</a>
        <a href="12user_profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="testimony.php" class="active"><i class="fas fa-user-friends"></i> Forum</a>
        <a href="4logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <div class="container">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="testimonial_text">What is on your mind?</label>
            <textarea name="testimonial_text" placeholder="Write here..." rows="4" required></textarea>
            <input type="submit" value="Submit">
        </form>

        <h3>Forums</h3>
        <div class="testimonial-container">
            <?php foreach ($recentTestimonies as $testimonial): ?>
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <p><strong><?php echo $testimonial['username']; ?></strong></p>
                        <?php if ($testimonial['username'] === $_SESSION['username']): ?>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="delete-form">
                                <input type="hidden" name="delete_testimonial_id" value="<?php echo $testimonial['testimony_id']; ?>">
                                <button type="submit" class="delete-icon"><i class="fas fa-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <p><?php echo $testimonial['testimonial_text']; ?></p>
                    <p>Date: <?php echo getTimeElapsedString($testimonial['date_posted']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

</body>

</html>