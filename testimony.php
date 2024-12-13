<?php
session_start();
require("0conn.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['username'])) {
        header("Location: 3login.php");
        exit();
    }

    $username = $_SESSION['username'];
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png">
    <style>
        body {
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f3f3;
            display: flex;
            flex-wrap: wrap;
        }

        .topnav {
            background-color: #16b978;
            overflow: hidden;
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            padding-top: 90px;
            transition: top 0.3s;
        }

        .topnav a {
            float: center;
            color: #f2f2f2;
            text-align: center;
            padding: 15px 25px;
            text-decoration: none;
            font-size: 17px;
            display: flex;
            align-items: center;
        }

        .topnav a:hover {
            background-color: #ddd;
            color: black;
        }

        .topnav a.active {
            background-color: #04AA6D;
            color: white;
        }

        .topnav a i {
            margin-right: 30px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
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
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 50px;
            padding: 20px;
            width: auto;
            margin-right: 10px;
        }

        .logo h1 {
            font-family: cursive;
            font-size: 24px;
            margin: 0;
            color: #16b978;
        }

        .container {
            background-color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 87vh;
            width: 100%;
            padding-top: 90px;
        }

        h2 {
            margin-bottom: 260px;
        }

        form {
            width: 80%;
            max-width: 600px;
            text-align: center;
        }

        label {
            text-align: left;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            margin-bottom: 15px;
            height: 100px;
            resize: none;
            margin-top: 10px;
        }

        input[type="submit"] {
            background-color:  #16b978;
            color: #f2f2f2;
            padding: 10px 15px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            height: 40px;
            width: 40%;
            font-weight: 500;
            font-size: 17px;
        }
        
        .button-secondary {
            margin-top: 30px;
                margin-right: 1350px;

                color: gray;
                padding: 8px 16px;
                text-decoration: none;
                border: none;
                font-size: 20px;
                background-color: transparent;
            }
    </style>
</head>
<body>
<div class="logo-container">
    <div class="logo">
        <img src="logo.png" alt="Tastebud Logo">
        <h1>Tastebud</h1>
    </div>
</div>

<div class="topnav">
    <a href="12user_profile.php"<?php echo (basename($_SERVER['PHP_SELF']) == '15userposts.php') ? 'class="active"' : ''; ?>><i class="fa fa-fw fa-user"></i>Profile</a>
    <a href="view_categories.php"><i class="fa-solid fa-list"></i>Categories</a>
    <a href="9customer.php"><i class="fa-solid fa-utensils"></i>User Recipes</a>
    <a href="14chat.php"><i class="fa-solid fa-comment"></i>Chat</a>
    <a href="4logout.php"><i class="fas fa-fw fa-sign-out"></i> Logout</a>
</div>

<div class="container">
<button class="button-secondary" onclick="window.location.href='12user_profile.php'">
    <i class="fas fa-arrow-left"></i>
</button>

    <h2>Write Testimonies</h2>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="testimonial_text">Your Testimony (up to 100 words):</label>
        <textarea name="testimonial_text" placeholder="Write here..." rows="4" cols="50" required></textarea>
        <input type="submit" value="Submit">
    </form>
</div>

</body>
</html>
