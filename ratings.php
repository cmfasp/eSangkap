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
        }

        h1 {
            margin-top: 100px;
            color: #c53b18;
            text-align: left;
        }

        .button-primary {
            background-color: darkred;
            color: white;
            cursor: pointer;
            text-decoration: none;
            width: 13%;
            align-items: center;
            border: none;
            border-radius: 30px;
            padding-top: 15px;
            padding-bottom: 15px;
            font-family: 'Poppins', sans-serif;
        }

        .button-primary:hover {
            background-color: #8b0000;
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

        .rating-section .rating-textarea {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 60%;
            /* Adjust width as needed */
            margin: 0 auto;
            /* Center the content */
        }

        .rating-section .rating-textarea select,
        .rating-section .rating-textarea textarea,
        .rating-section .rating-textarea button {
            width: 100%;
            /* Ensure they take up the same width */
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
            font-size: 16px;
            background-color: #fff;
            font-family: 'Poppins', sans-serif;
        }

        .rating-section .rating-textarea select {
            width: 100%;
        }

        .rating-section .rating-textarea textarea {
            width: 97%;
            height: 70px;
            background-color: #f3f3f3;
            resize: none;/
        }

        .rating-section .rating-textarea button {
            width: 40%;
            background-color: darkred;
            color: white;
            cursor: pointer;
            text-decoration: none;
            border: none;
            border-radius: 30px;
            padding-top: 15px;
            padding-bottom: 15px;
            font-family: 'Poppins', sans-serif;
        }

        .rating-section .rating-textarea button:hover {
            background-color: #8b0000;
        }

        .rating-textarea select,
        .rating-textarea textarea,
        .rating-textarea button {
            max-width: 200x;
            width: 100%;
        }

        .rating-section {
            text-align: center;
        }

        .rating-stars {
            font-size: 30px;
            color: #ccc;
            cursor: pointer;
        }

        .rating-stars.selected {
            color: yellow;
        }

        .rating-stars .star {
            font-size: 30px;
            color: #ccc;
            cursor: pointer;
            margin: 0 5px;
            transition: color 0.2s ease-in-out;
        }

        .rating-stars .star.selected {
            color: #ffcc00;
            /* Highlight selected stars */
        }

        .ratings-container {
            display: flex;
            width: 60%;
            flex-direction: column;
            gap: 20px;
            margin: 20px auto;
            align-self: center;
        }

        .rating-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .rating-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .rating-username {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .rating-stars i {
            color: #ddd;
            margin-right: 5px;
            font-size: 18px;
        }

        .rating-stars i.filled {
            color: #ffcc00;
        }

        .rating-comment {
            font-size: 20px;
            font-weight: bolder;
            color: #666;
            margin: 10px 0;
        }

        .rating-date {
            font-size: 12px;
            color: #999;
        }

        .delete-rating {
            font-size: 18px;
            color: darkred;
            text-decoration: none;
            margin-top: 10px;
            display: inline-block;
        }

        .delete-rating:hover {
            color: #8b0000;
        }

        .no-ratings {
            font-size: 14px;
            color: #aaa;
            text-align: center;
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
        <a href="favoritescreen.php"><i class="fa-solid fas fa-heart"></i>Favorites</a>
        <a href="view_categories.php" class="active"><i class="fa-solid fa-list"></i>Categories</a>
        <a href="12user_profile.php"><i class="fas fa-user"></i>Profile</a>
        <a href="about_us.php"><i class="fa-solid fa-info-circle"></i>About Us</a>
        <a href="4logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </div>

    <div class="container">
        <form method="post" action="">
            <h1>Hi <b class="meal-username"><?php echo $_SESSION['username']; ?></b>, you can now rate this meal!</h1>
            <?php foreach ($images as $image): ?>
                <img class="meal-image" src="<?php echo $image['image_link']; ?>" alt="Meal Image">
            <?php endforeach; ?>

            <h2>Ratings:</h2>
            <div class="ratings-container">
                <?php if (count($allRatings) > 0): ?>
                    <?php foreach ($allRatings as $rating): ?>
                        <div class="rating-card">
                            <div class="rating-header">
                                <strong class="rating-username"><?php echo $rating['username']; ?></strong>
                                <span class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa fa-star <?php echo $i <= $rating['rating_value'] ? 'filled' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </span>
                            </div>
                            <p class="rating-comment"><?php echo $rating['rating_comment']; ?></p>
                            <p class="rating-date"><i class="fa fa-calendar"></i> <?php echo $rating['date_rated']; ?></p>
                            <?php if ($rating['username'] == $_SESSION['username']): ?>
                                <a href="?meal_id=<?php echo $meal_id; ?>&delete_rating_id=<?php echo $rating['rating_id']; ?>" class="delete-rating">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-ratings">No ratings available for this meal.</p>
                <?php endif; ?>
            </div>


            <div class="rating-section">
                <h3>Rate this Meal:</h3>
                <div class="rating-stars" id="rating-stars">
                    <span class="star" data-value="1">&#9733;</span>
                    <span class="star" data-value="2">&#9733;</span>
                    <span class="star" data-value="3">&#9733;</span>
                    <span class="star" data-value="4">&#9733;</span>
                    <span class="star" data-value="5">&#9733;</span>
                </div>
                <textarea name="rating_comment" placeholder="Write a comment..." rows="4" cols="50" required></textarea>
                <input type="hidden" name="rating_value" id="rating_value"><br>
                <button class="button-primary" type="submit" name="submit">Submit</button>
            </div>
        </form>
    </div>

    <script>
        document.querySelectorAll('.star').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.getAttribute('data-value');
                document.getElementById('rating_value').value = rating;

                document.querySelectorAll('.star').forEach(star => {
                    if (star.getAttribute('data-value') <= rating) {
                        star.classList.add('selected');
                    } else {
                        star.classList.remove('selected');
                    }
                });
            });
        });
    </script>
</body>

</html>