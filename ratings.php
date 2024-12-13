<?php
session_start();
require("0conn.php");

// Check if meal_id is set in the URL
if (isset($_GET['meal_id'])) {
    $meal_id = $_GET['meal_id'];

    // Check if the user is logged in
    if (!isset($_SESSION['username'])) {
        header("Location: 9customer.php");
        exit();
    }

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch meal details
        $fetchMealStmt = $pdo->prepare("SELECT * FROM meals WHERE meal_id = ?");
        $fetchMealStmt->execute([$meal_id]);
        $meal = $fetchMealStmt->fetch(PDO::FETCH_ASSOC);

        // Fetch meal images
        $fetchImagesStmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ?");
        $fetchImagesStmt->execute([$meal_id]);
        $images = $fetchImagesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all ratings
        $fetchAllRatingsStmt = $pdo->prepare("SELECT * FROM ratings WHERE meal_id = ?");
        $fetchAllRatingsStmt->execute([$meal_id]);
        $allRatings = $fetchAllRatingsStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        // Check if the submitted form is for adding a new rating
        if (isset($_POST['rating_value'])) {
            $rating_value = filter_input(INPUT_POST, 'rating_value', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 5)));
            $rating_comment = $_POST['rating_comment']; // Get the rating comment

            if ($rating_value !== false) {
                // Check if the user has already rated this meal
                $existingRatingStmt = $pdo->prepare("SELECT * FROM ratings WHERE meal_id = ? AND username = ?");
                $existingRatingStmt->execute([$meal_id, $_SESSION['username']]);
                $existingRating = $existingRatingStmt->fetch(PDO::FETCH_ASSOC);

                // Insert or update the rating
                if (!$existingRating) {
                    $insertRatingStmt = $pdo->prepare("INSERT INTO ratings (meal_id, username, rating_value, rating_comment, date_rated) VALUES (?, ?, ?, ?, NOW())");
                    $insertRatingStmt->execute([$meal_id, $_SESSION['username'], $rating_value, $rating_comment]);
                }
            }
        }
        $fetchRatingsStmt = $pdo->prepare("SELECT * FROM ratings WHERE meal_id = ?");
        $fetchRatingsStmt->execute([$meal_id]);
        $ratings = $fetchRatingsStmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    header("Location: 12user_profile.php");
    exit();
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

        .container {
            width: 100%;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-top: 120px;
            display: flex;
            justify-content: center;
            flex-direction: column;
            height: auto;
            text-align: center;
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

        .button-primary {
            background-color: #16b978;
            color: white;       
            cursor: pointer;
            text-decoration: none;
            width: 13%;
            align-items: center;
            border: none; /* Removed border */
            border-radius:30px;
            margin-top: 5px;
            padding-top: 15px;
            padding-bottom: 15px;
        }

        .button-primary:hover {
            background-color: #128d63;
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

        h2 {
            text-align: center;
            margin-top: 5px;
        }

        .meal-image {
            width: 59%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: auto;
            align-self: flex-start;
        }

        p {
            text-align: center;
            color: lightgray;
            font-family: sans-serif;
            font-weight: 500;
            margin-top: 30px;
            margin-bottom: 20px;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        li {
            margin-top: 30px;
            margin-bottom: 25px;
            text-align: left;
            margin-left: 300px;
        }


        .button-secondary {
            margin-top: 30px;
            margin-left: 25px;
            margin-bottom: 20px;
            color: gray;
            padding: 5px;
            text-decoration: none;
            display: flex;
            border: none;
            font-size: 20px;
            background-color: transparent;
        }

        .rating-section .rating-textarea select {
            width: 15%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            background-color: #fff;
            font-size: 16px;
            color: #333;
        }

        /* Style for the dropdown arrow */
        .rating-section .rating-textarea select::after {
            content: '\25BC'; /* Unicode character for down arrow */
            position: absolute;
            transform: translateY(-50%);
            pointer-events: none;
        }

        /* Style for the dropdown arrow in Firefox */
        .rating-section .rating-textarea select::-ms-expand {
            display: none;
        }
        .rating-textarea textarea {
            border: none;
            width: 59%; /* Same width as the image */
            height: 50px; /* Same height as the image */
            object-fit: cover;
            border-radius: 5px;
            margin-right: auto;
            align-self: flex-start;
            padding: 10px; /* Add padding for better appearance */
            box-sizing: border-box; /* Include padding and border in the total width and height */
             /* Add border for a neat look */
            margin-bottom: px; /* Adjusted margin */
            background-color: #f3f3f3;
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
        <a href="12user_profile.php"<?php echo (basename($_SERVER['PHP_SELF']) == '15userposts.php') ? 'class="active"' : ''; ?>>
            <i class="fa fa-fw fa-user"></i>Profile
        </a>
        <a href="view_categories.php">
            <i class="fas fa-fw fa-user"></i>Categories
        </a>
        <a href="9customer.php">
            <i class="fa-solid fa-utensils"></i>User Recipes
        </a>
        <a href="14chat.php">
            <i class="fa-solid fa-comment"></i>Chat
        </a>
        <a href="4logout.php">
            <i class="fas fa-fw fa-sign-out"></i> Logout
        </a>
    </div>

    <div class="container">
        <button class="button-secondary" onclick="window.location.href='11meal_details_comments.php?meal_id=22'">
            <i class="fas fa-arrow-left"></i>
        </button>
        <form method="post" action="">
            <h2>Meal Ratings</h2>
            <!-- Display meal image -->
            <?php foreach ($images as $image): ?>
                <img class="meal-image" src="<?php echo $image['image_link']; ?>" alt="Meal Image">
            <?php endforeach; ?>
            <?php if (count($allRatings) > 0): ?>
                <ul>
                    <?php foreach ($allRatings as $rating): ?>
                        <li>
                            <strong><?php echo $rating['username']; ?>:</strong>
                            <?php echo $rating['rating_comment']; ?><br>
                            <strong>Rating:</strong> <?php echo $rating['rating_value']; ?><br>
                            <strong>Date Rated:</strong> <?php echo $rating['date_rated']; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No ratings available for this meal.</p>
            <?php endif; ?>

            <div class="rating-section">
                <div class="rating-textarea">
                    <label for="rating_value">Rate this Meal:</label>
                    <select name="rating_value" required>
                        <option value="1">1 - Very Bad</option>
                        <option value="2">2 - Bad</option>
                        <option value="3">3 - Average</option>
                        <option value="4">4 - Good</option>
                        <option value="5">5 - Excellent</option>
                    </select>
                    <br><br>

                    <!-- Adjusted textarea style -->
                    <textarea name="rating_comment" placeholder="Write a comment..." rows="4" cols="50" required></textarea>
                    <br><br>

                    <!-- Moved the submit button below the textarea -->
                    <button class="button-primary" type="submit" name="submit">Submit</button>
                </div>
            </div>
        </form>
    </div>
</body>

</html>

