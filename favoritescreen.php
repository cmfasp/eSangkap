<?php
session_start();
date_default_timezone_set('Asia/Manila');
require('0conn.php');  

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    echo "Please log in to view your favorite meals.";
    exit;  // Stops the rest of the page from loading if the user is not logged in
}

$username = $_SESSION['username'];  

$sql = "SELECT m.*, f.date_created, c.category_name, 
        (SELECT image_link FROM meal_images WHERE meal_id = m.meal_id LIMIT 1) AS meal_image
        FROM favorites f
        JOIN meals m ON f.meal_id = m.meal_id
        JOIN categories c ON m.category_id = c.category_id
        WHERE f.username = ? 
        ORDER BY f.date_created DESC";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    die("Error preparing query: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$favorites = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_stmt_close($stmt);
mysqli_close($conn);


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


if (isset($_GET['meal_id'])) {
    $meal_id = $_GET['meal_id'];

    // Increment view count for the specific meal
    $update_sql = "UPDATE meals SET views = views + 1 WHERE meal_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);

    if ($update_stmt === false) {
        die("Error preparing query: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($update_stmt, 'i', $meal_id);
    mysqli_stmt_execute($update_stmt);

    mysqli_stmt_close($update_stmt);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Favorites</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
         body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            background-color: #f04e23;
            margin-top: 65px;
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            overflow-x: hidden;
            padding-top: 30px;
            display: flex;
            flex-direction: column;
        }

        .logo-container {
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            padding: 10px;
        }


      

        .logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .title {
            color: #f04e23;
            font-size: 24px;
            font-weight: bold;
            text-align: left;
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

        .sidebar a:hover {
            background-color: white;
            color: darkred;
        }

        .sidebar a.active {
            background-color: #ffcccb;
            color: darkred;
        }

        .sidebar a i {
            margin-right: 15px;
        }

        .container {
            margin-left: 250px;
            padding: 20px;
            background-color: #fff;
        }
 

        .recipe-box {
            box-sizing: border-box;
            float: left;
            padding: 10px;
            border-radius: 15px;
            background: white;
            margin: 10px;
            justify-content: space-evenly;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
            width: calc(33.33% - 20px);
            box-sizing: border-box;
            margin-bottom: 10px;
        }

        .recipe-box img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 10px;
        }

        h1 {
            margin-top: 100px;
            color: #c53b18;
            text-align: left;
        }

        .button-primary {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            padding: 8px 16px;
            background-color: darkred;
            color: #fff;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>

<body>
<div class="logo-container">
            <img src="logo.jpg" alt="Logo" class="logo">
            <h2 class="title">eSangkap</h2>
        </div>

    <div class="sidebar">
        <a href="9customer.php"><i class="fa fa-fw fa-home"></i>Home</a>
        <a href="favoritescreen.php"class="active"><i class="fa-solid fas fa-heart"></i>Favorites</a>
        <a href="view_categories.php"><i class="fa-solid fa-list"></i>Categories</a>
        <a href="12user_profile.php"><i class="fas fa-user"></i>Profile</a>
        <a href="testimony.php"><i class="fas fa-user-friends"></i> Forum</a>
        
        <a href="4logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </div>

    <div class="container">
    <h1>My Favorite Meals</h1>
    <?php if (!empty($favorites)): ?>
    <div class="clearfix">
        <?php foreach ($favorites as $meal): ?>
            <div class="recipe-box">
                <div style="position: relative;">
                    <!-- Delete button (Trash icon) -->
                    <a href="delete_favorites.php?meal_id=<?php echo $meal['meal_id']; ?>"
                       onclick="return confirm('Are you sure you want to remove this meal from your favorites?');"
                       style="position: absolute; top: 10px; right: 10px; color: white; font-size: 20px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);">
                        <i class="fas fa-trash"></i>
                    </a>
                    <img src="<?php echo htmlspecialchars($meal['meal_image'] ?? 'uploads/default.jpg'); ?>" alt="Meal">
                </div>
                <h3><?php echo htmlspecialchars($meal['meal_name']); ?></h3>
                <p><strong><?php echo htmlspecialchars($meal['username']); ?></strong></p>
                <p><b>Description:</b> 
                    <?php echo strlen($meal['description']) > 100 ? substr(htmlspecialchars($meal['description']), 0, 100) . '...' : htmlspecialchars($meal['description']); ?>
                </p>
                <p>Views: <?php echo htmlspecialchars($meal['views']); ?></p>
                <p>Date: <?php echo getTimeElapsedString($meal['date_created']); ?></p>
                <a href="11meal_details_comments.php?meal_id=<?php echo $meal['meal_id']; ?>" class="button-primary">View Details</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>You have no favorite meals yet.</p>
<?php endif; ?>
