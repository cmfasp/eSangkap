<?php
session_start();
require("0conn.php");

// Check if meal_id is set in the URL
if (isset($_GET['meal_id'])) {
    $meal_id = $_GET['meal_id'];

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

        // Handle rating submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
            if (!isset($_POST['rating_value']) || $_POST['rating_value'] < 1 || $_POST['rating_value'] > 5) {
                die("Error: Invalid rating value.");
            }

            $rating_value = filter_input(INPUT_POST, 'rating_value', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 5)));
            $rating_comment = trim($_POST['rating_comment']);

            // Check if rating_value is valid
            if ($rating_value === false) {
                die("Error: Invalid rating value.");
            }

            // Check if comment is provided
            if (empty($rating_comment)) {
                die("Error: Please write a comment.");
            }
            if (isset($_POST['rating_value'])) {
                $rating_value = filter_input(INPUT_POST, 'rating_value', FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 5)));
                $rating_comment = $_POST['rating_comment'];

                if ($rating_value !== false) {
                    $existingRatingStmt = $pdo->prepare("SELECT * FROM ratings WHERE meal_id = ? AND username = ?");
                    $existingRatingStmt->execute([$meal_id, $_SESSION['username']]);
                    $existingRating = $existingRatingStmt->fetch(PDO::FETCH_ASSOC);

                    if (!$existingRating) {
                        $insertRatingStmt = $pdo->prepare("INSERT INTO ratings (meal_id, username, rating_value, rating_comment, date_rated) VALUES (?, ?, ?, ?, NOW())");
                        $insertRatingStmt->execute([$meal_id, $_SESSION['username'], $rating_value, $rating_comment]);
                    } else {
                        // Update existing rating if user has already rated the meal
                        $updateRatingStmt = $pdo->prepare("UPDATE ratings SET rating_value = ?, rating_comment = ?, date_rated = NOW() WHERE meal_id = ? AND username = ?");
                        $updateRatingStmt->execute([$rating_value, $rating_comment, $meal_id, $_SESSION['username']]);
                    }
                }
            }
            // Re-fetch ratings after submit
            $fetchAllRatingsStmt->execute([$meal_id]);
            $allRatings = $fetchAllRatingsStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Handle delete rating
        if (isset($_GET['delete_rating_id'])) {
            $deleteRatingId = $_GET['delete_rating_id'];
            $deleteRatingStmt = $pdo->prepare("DELETE FROM ratings WHERE rating_id = ? AND username = ?");
            $deleteRatingStmt->execute([$deleteRatingId, $_SESSION['username']]);

            // Re-fetch ratings after deletion
            $fetchAllRatingsStmt->execute([$meal_id]);
            $allRatings = $fetchAllRatingsStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
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
            padding: 15px;
            position: fixed;
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

        .logo-container {
            text-align: left;
            padding-bottom: 20px;
            display: flex;
            align-items: center;
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
            text-align: left;
        }


        h3 {
            margin-top: 100px;
            color: #c53b18;
            text-align: left;
            font-size: 22px;
        }

        h1 {

            font-size: 17px;
            margin-right: 10px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
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
            display: block;
            margin-left: auto;
            margin-bottom: 30px;
        }

        p {
            text-align: center;
            color: lightgray;
            font-family: sans-serif;
            font-weight: 500;
            margin-top: 30px;
            margin-bottom: 20px;
        }

        .rating-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
            flex-direction: column;
        }

        .rating-stars {
            display: flex;
            margin-bottom: 10px;
        }

        .rating-stars .star {
            font-size: 30px;
            color: #ccc;
            cursor: pointer;
            margin-right: 5px;
        }

        .rating-stars .star.selected {
            color: yellow;
        }

        .rating-stars .star:hover {
            color: gold;
        }

        textarea {
            width: 57%;
            /* Set the width to make it narrower */
            padding: 10px;
            border-radius: 15px;
            border: 1px solid #ccc;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }

        button {
            width: calc(40% - 30px);
            padding: 10px;
            background-color: darkred;
            color: white;
            border-radius: 20px;
            border: none;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            cursor: pointer;
            text-align: center;
            margin-top: 20px;

        }

        button:hover {
            background-color: #8b0000;
        }

        .ratings-container {
            margin-left: 20px;
            /* Space from the left */
            padding: 20px;
            /* Padding around the container */
            width: 60%;
            /* Set the width to make it narrower */
            margin-right: auto;
            margin-left: auto;
            /* Center the container */
        }

        .rating-item {
            background-color: #f4f4f4;
            /* Light background */
            padding: 10px;
            border-radius: 15px;
            /* Rounded corners */
            margin-bottom: 10px;
            /* Space between rating items */
            position: relative;
            /* Allows absolute positioning inside */
        }

        .rating-item:last-child {
            border-bottom: none;
            /* Remove the bottom border for the last item */
        }

        .delete-rating {
            position: absolute;
            /* Positions the delete button */
            top: 10px;
            /* Places it at the top */
            right: 10px;
            /* Places it at the right */
            color: gray;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="logo-container">
        <img src="logo.jpg" alt="Logo" class="logo">
        <h2 class="title">eSangkap</h2>
    </div>

    <div class="sidebar">
        <a href="9customer.php" class="active"><i class="fa fa-fw fa-home"></i>Home</a>
        <a href="favoritescreen.php"><i class="fa-solid fas fa-heart"></i>Favorites</a>
        <a href="view_categories.php"><i class="fa-solid fa-list"></i>Categories</a>
        <a href="12user_profile.php"><i class="fas fa-user"></i>Profile</a>
        <a href="testimony.php"><i class="fas fa-user-friends"></i> Forum</a>
        <a href="4logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </div>

    <div class="container">
        <form method="post" action="">
            <h3>Hi <b class="meal-username"><?php echo $_SESSION['username']; ?></b>, you can now rate this meal!</h3>
            <?php foreach ($images as $image): ?>
                <img class="meal-image" src="<?php echo $image['image_link']; ?>" alt="Meal Image">
            <?php endforeach; ?>

            <?php if (count($allRatings) > 0): ?>
                <div class="ratings-container">

                    <?php foreach ($allRatings as $rating): ?>
                        <div class="rating-item">
                            <strong><?php echo $rating['username']; ?>:</strong>
                            <?php echo $rating['rating_comment']; ?><br>
                            <?php if ($rating['username'] == $_SESSION['username']): ?>
                                <a href="?meal_id=<?php echo $meal_id; ?>&delete_rating_id=<?php echo $rating['rating_id']; ?>" class="delete-rating">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            <?php endif; ?>
                            <strong>Rating:</strong>
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating['rating_value']) {
                                    echo "⭐";
                                } else {
                                    echo "☆";
                                }
                            }
                            ?><br>
                            <strong>Date:</strong> <?php echo $rating['date_rated']; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>


            <div class="rating-section">
                <div class="rating-stars" id="rating-stars">
                    <h1>Rate this Meal</h1>
                    <span class="star" data-value="1">&#9733;</span>
                    <span class="star" data-value="2">&#9733;</span>
                    <span class="star" data-value="3">&#9733;</span>
                    <span class="star" data-value="4">&#9733;</span>
                    <span class="star" data-value="5">&#9733;</span>
                </div>
                <textarea name="rating_comment" placeholder="Write a comment..." rows="4" required></textarea>
                <input type="hidden" name="rating_value" id="rating_value">
                <button type="submit" name="submit">Submit</button>
            </div>


            <script>
                // Select all star elements and add click event listeners
                document.querySelectorAll('.star').forEach(star => {
                    star.addEventListener('click', function() {
                        const rating = this.getAttribute('data-value'); // Get the value of the clicked star
                        document.getElementById('rating_value').value = rating; // Set the hidden input field to the selected rating value

                        // Loop through each star and add/remove the 'selected' class
                        document.querySelectorAll('.star').forEach(star => {
                            if (star.getAttribute('data-value') <= rating) {
                                star.classList.add('selected'); // Make stars up to the clicked one yellow
                            } else {
                                star.classList.remove('selected'); // Reset the remaining stars to default color
                            }
                        });
                    });
                });

                document.querySelector('button[type="submit"]').addEventListener('click', function(event) {
                    const ratingValue = document.getElementById('rating_value').value;
                    const comment = document.querySelector('textarea[name="rating_comment"]').value;

                    if (!ratingValue) {
                        alert('Please select a rating!');
                        event.preventDefault(); // Prevent form submission if no rating is selected
                        return false;
                    }

                    if (comment.trim() === '') {
                        alert('Please write a comment!');
                        event.preventDefault(); // Prevent form submission if no comment is entered
                        return false;
                    }
                });
            </script>
</body>

</html>