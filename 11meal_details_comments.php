<?php
session_start();
require("0conn.php");

// Check if the user is logged in.
// Kapag hindi pa naka-login ang user at gusto niyang i-access yung page, ire-redirect siya sa login page.
// Magsho-show ng error message na "You must log in first."
if (!isset($_SESSION["username"])) {
    $_SESSION['error_message'] = "You must log in first.";
    header("Location: 3login.php");
    exit();  // Tumigil agad ang proseso para hindi magpatuloy
}

if (isset($_GET['meal_id'])) {
    // Get the meal_id from the URL to fetch the corresponding meal details.
    $meal_id = $_GET['meal_id'];

    // Connect to the database using PDO.
    // Kapag walang connection sa database, mag-eerror ito, kaya ginagamit ang try-catch block.
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());  // Kapag may error, ititigil ang script at magpapakita ng error message.
    }

    // Fetch the meal details based on the meal_id from the meals table.
    $stmt = $pdo->prepare("SELECT * FROM meals WHERE meal_id = ?");
    $stmt->execute([$meal_id]);
    $meal = $stmt->fetch(PDO::FETCH_ASSOC);  // Kinukuha ang meal information

    // Fetch the cooking instructions related to this meal and order by step number.
    $instructionsStmt = $pdo->prepare("SELECT * FROM instructions WHERE meal_id = ? ORDER BY step_number");
    $instructionsStmt->execute([$meal_id]);
    $instructions = $instructionsStmt->fetchAll(PDO::FETCH_ASSOC);  // Kinuha lahat ng mga instructions

    // Fetch the ingredients for the meal.
    $ingredientsStmt = $pdo->prepare("SELECT * FROM ingredients WHERE meal_id = ?");
    $ingredientsStmt->execute([$meal_id]);
    $ingredients = $ingredientsStmt->fetchAll(PDO::FETCH_ASSOC);  // Kinuha lahat ng ingredients

    // Fetch the comments made on this meal, ordered by the most recent.
    $commentsStmt = $pdo->prepare("SELECT * FROM comments WHERE meal_id = ? ORDER BY created_at DESC");
    $commentsStmt->execute([$meal_id]);
    $comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);  // Kinuha ang mga comments na may kinalaman sa meal

    // Fetch the images related to the meal.
    $imagesStmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ?");
    $imagesStmt->execute([$meal_id]);
    $images = $imagesStmt->fetchAll(PDO::FETCH_ASSOC);  // Kinuha ang mga images ng meal

    // Increment the views for the meal every time it is viewed.
    // Madadagdagan ang 'views' ng meal para makita kung ilan na ang nag-view sa meal.
    $incrementViewsStmt = $pdo->prepare("UPDATE meals SET views = views + 1 WHERE meal_id = ?");
    $incrementViewsStmt->execute([$meal_id]);

    // Fetch nutritional information for the meal.
    $nutriInfoStmt = $pdo->prepare("SELECT * FROM nutritional_info WHERE meal_id = ?");
    $nutriInfoStmt->execute([$meal_id]);
    $nutriInfo = $nutriInfoStmt->fetchAll(PDO::FETCH_ASSOC);  // Kinuha ang nutritional info ng meal

    // Fetch meal data again after incrementing the views to reflect the latest meal data.
    $stmt = $pdo->prepare("SELECT * FROM meals WHERE meal_id = ?");
    $stmt->execute([$meal_id]);
    $meal = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch alternative ingredients if available.
    // Hinahanap ang mga alternate na ingredients kung mayroon.
    $altIngredientsStmt = $pdo->prepare("
    SELECT alt_ingredients 
    FROM ingredients 
    WHERE meal_id = ? 
      AND alt_ingredients IS NOT NULL
");
    $altIngredientsStmt->execute([$meal_id]);
    $alternative_ingredients = $altIngredientsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter out invalid or empty alternative ingredients.
    // Tinatanggal ang mga walang halaga o hindi valid na alternative ingredients.
    $filtered_ingredients = array_filter($alternative_ingredients, function ($item) {
        return !empty($item['alt_ingredients']) && $item['alt_ingredients'] !== '0';
    });

} else {
    // If the meal_id is not set, redirect to the customer page.
    // Kapag wala ang meal_id, ire-redirect ang user sa customer page.
    header("Location: 9customer.php");
    exit();
}

$userLoggedIn = isset($_SESSION['username']);
$allowComments = $userLoggedIn;  // Only allow comments if the user is logged in.

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userLoggedIn) {
    // If the form is submitted and the user is logged in, handle the comment submission.
    if (isset($_POST['comment'])) {
        $comment_text = $_POST['comment'];  // Get the comment text from the POST request.
        // Insert the comment into the database.
        $insertStmt = $pdo->prepare("INSERT INTO comments (meal_id, user_name, comment_text) VALUES (?, ?, ?)");
        $insertStmt->execute([$meal_id, $_SESSION['username'], $comment_text]);

        // Redirect to the same page to refresh the meal details with the new comment.
        header("Location: 11meal_details_comments.php?meal_id=$meal_id");
        exit();
    } elseif (isset($_POST['delete_comment'])) {
        // If the delete_comment button is clicked, try to delete the comment.
        $comment_id = $_POST['delete_comment'];

        // Fetch the comment to be deleted.
        $commentStmt = $pdo->prepare("SELECT * FROM comments WHERE comment_id = ?");
        $commentStmt->execute([$comment_id]);
        $commentToDelete = $commentStmt->fetch(PDO::FETCH_ASSOC);

        // Check if the logged-in user is the one who posted the comment.
        // Kung ang user ay ang may-ari ng comment, papayagan siyang mag-delete.
        if ($commentToDelete && $_SESSION['username'] === $commentToDelete['user_name']) {
            // If the comment belongs to the logged-in user, ask for confirmation before deleting.
            echo "<script>
                    let confirmDelete = confirm('Are you sure you want to delete your comment?');
                    if (confirmDelete) {
                        window.location.href = 'delete_comment.php?comment_id=$comment_id&meal_id=$meal_id';
                    }
                 </script>";
        }
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
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

        h1,
        h3 {
            font-weight: bold;
            margin-top: 20px;
            margin-left: 60px;
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

        .sidebar a.active {
            background-color: #ffcccb;
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
            background-color: #fff;
            width: 60%;
            margin-right: 900px;

            justify-content: center;
            margin: 40px auto;
            padding: 20px;
            border-radius: 10px;
        }


        .views {

            font-size: 16px;
            background-color: #f04e23;
            color: white;
            border-radius: 20px;
            width: 10%;
            padding: 15px;
            margin-left: 875px;
            display: flex;
            align-items: center;
            justify-content: center;
        }


        button {
            background-color: darkred;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            margin-top: 10px;
        }

        .comment-item {
            border-radius: 5px;
            margin: 20px 0 0 40px;
            background-color: #f3f3f3;
            padding: 15px;
            list-style: none;
            display: flex;
            flex-direction: column;
            word-wrap: break-word;
            max-width: 100%;
        }


        .comment-header {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .comment-text-wrapper {
            flex-grow: 1;
        }

        .comment-text {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            word-wrap: break-word;
            max-width: 90%;
            margin-left: 20px;
        }

        .comment-info {
            font-size: 14px;
            color: #555;
            margin-top: 5px;
            margin-left: 20px;
        }

        .delete-form {
            margin-left: 10px;
            display: flex;
            width: 50px;
        }

        .delete-comment-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            color: grey;
        }

        .comment-form {
            display: flex;
            width: calc(150% - 100px);
            margin-top: 15px;
        }

        .comment-form textarea {
            width: calc(150% - 50px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: none;
            word-wrap: break-word;
            margin-right: 60px;
        }

        .submit-comment-btn {
            background-color: #f04e23;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 25px 30px;
            cursor: pointer;
            margin-left: 15px;
            font-size: 16px;
        }

        .comments-list {
            padding: 0;
            margin: 0;
            list-style-type: none;
        }

        form {
            margin-top: 20px;
            width: 100%;
            display: flex;
        }

        form textarea {
            width: 120%;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-left: 60px;
        }

        .button-success {
            margin-left: 60px;
            color: white;
            padding: 8px 16px;
            display: inline-block;
            border: none;
            font-size: 16px;
            text-align: center;
        }

        .button-secondary {
            margin-top: 140px;
            margin-left: 60px;
            color: grey;
            padding: 8px 16px;
            display: inline-block;
            border: none;
            font-size: 16px;
            text-align: center;
            background-color: transparent;
            margin-bottom: -100%;
        }

        img {
            margin-bottom: 20px;
            margin-left: 60px;
            width: 95%;
            height: 400px;
            object-fit: cover;
            border-radius: 30px;
        }

        p {
            margin-left: 60px;
        }

        .meal-details-box {
            margin-top: 60px;
            align-items: center;
        }

        .meal-details-box h1,
        .meal-details-box p,
        .meal-details-box button {
            margin-left: 60px;
        }

        ol.rounded-list {
            counter-reset: li;
        }

        .list-box ol.rounded-list li {
            position: relative;
            padding: 15px;
            background: #f3f3f3;
            border-radius: 5px;
            margin-top: 12px;
            margin-left: 60px;
            list-style: none;
        }

        .list-box ol.rounded-list li:before,
        .instructions ol.rounded-list li:before {
            content: counter(li);
            counter-increment: li;
            position: absolute;
            left: -2em;
            top: 50%;
            margin-top: -1em;
            background: #f04e23;
            height: 30px;
            width: 30px;
            line-height: 30px;
            border: 5px solid #fff;
            text-align: center;
            font-weight: bold;
            border-radius: 2em;
            transition: all .3s ease-out;
            color: #fff;
        }

        .watch-video {
            display: inline-block;
            padding: 10px 16px;
            background-color: #f04e23;
            color: #fff;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .watch-video:hover {
            background-color: darkred;
            color: white;
        }

        .watch-video i {
            margin-right: 8px;
        }

        .meal-header {
            display: flex;
            justify-content: space-between;
            margin-left: 60px;
        }

        .meal-header h1 {
            margin: 0;
            font-size: 24px;
        }



        .button {
            margin-left: 30px;
            border: 2px #f04e23;
            padding: 8px 30px;
            font-size: 17px;
            font-family: 'Poppins', sans-serif;
            background-color: white;
            color: black;
            cursor: pointer;
            border-radius: 20px;
        }

        .button:hover {
            border: 2px #f04e23;
            background-color: white;
            color: lightgreen;
        }

        .row {
            white-space: nowrap;
            display: flex;
            align-items: center;
        }

        .text {
            margin-right: 10px;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .username {
            font-weight: bold;
            color: rgb(228, 33, 33);
            font-size: 1.1em;
        }

        .views {
            display: flex;
            font-size: 14px;
            background-color: #f04e23;
            color: white;
            border-radius: 20px;
            padding: 8px 15px;
            gap: 8px;
            white-space: nowrap;
            max-width: 100%;
        }
        .username {
            font-weight: bold;
            margin-top: 70px; 
            margin-left: 60px;
            font-size: 23px;
        }


        @media (max-width: 768px) {
            .views {
                font-size: 12px;
                padding: 6px 10px;
            }

            .info-row {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .views {
                font-size: 10px;
                padding: 5px 8px;
            }

            .info-row {
                flex-direction: column;
                gap: 5px;
                text-align: center;
            }
        }

        .invisible-margin {
            visibility: hidden;
        }
    </style>
</head>

<body>
    <div class="logo-container">
        <img src="logo.jpg" alt="Logo" class="logo">
        <h2 class="title">eSangkap</h2>
    </div>

    <div class="sidebar">
        <a href="9customer.php" class="active"><i class="fa fa-fw fa-home"></i>Home</a><?php echo (basename($_SERVER['PHP_SELF']) == '9customer.php') ? 'class="active"' : ''; ?>
        <a href="favoritescreen.php"><i class="fa-solid fas fa-heart"></i>Favorites</a>
        <a href="view_categories.php"><i class="fa-solid fa-list"></i>Categories</a>
        <a href="12user_profile.php"><i class="fas fa-user"></i>Profile</a>
        <a href="testimony.php"><i class="fas fa-user-friends"></i> Forum</a>
        <a href="4logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </div>

    <div class="container">
    <h2 class="username"> <?php echo $meal['username']; ?></p>
            <h2>
                <?php foreach ($images as $image): ?>
                    <img src="<?php echo htmlspecialchars($image['image_link']); ?>">
                <?php endforeach; ?>

                <div class="meal-header">
                    <div>
                        <h1><?php echo $meal['meal_name']; ?></h1>

                    </div>
                    <div>
                        <a class="watch-video" href="shoppingList.php?meal_id=<?php echo $meal_id; ?>" target="_blank">
                            <i class="fas fa-shopping-cart"></i> Where to Buy Ingredients
                        </a>
                        <a class="watch-video" href="<?php echo $meal['video_link']; ?>" target="_blank">
                            <i class="fas fa-play-circle"></i> Watch Video
                        </a>
                    </div>

                </div>
                <h3>Description: </h3>
                <p><?php echo $meal['description']; ?></p>
                <p class="views">
                    <i class="fas fa-eye"></i> Views: <?php echo $meal['views']; ?>
                </p>
                <div class="buttons">
                    <button class="button" id="toggle-alt-ingredients">Show Alternative Ingredients &#9660;</button>
                </div>
                <div class="list-box">
                    <ol class="rounded-list">
                        <?php foreach ($ingredients as $ingredient) { ?>
                            <li>
                                <?php echo $ingredient['ingredient_name']; ?>
                                <?php if (!empty($ingredient['alt_ingredients'])) { ?>
                                    <br><span class="alt-ingredient" style="font-size: 0.9rem; color: #888; display: none;">Alternative: <?php echo $ingredient['alt_ingredients']; ?></span>
                                <?php } ?>
                            </li>
                        <?php } ?>
                    </ol>
                </div>

                <script>
                    document.getElementById("toggle-alt-ingredients").addEventListener("click", function() {
                        // Get all alternative ingredients elements
                        const altIngredients = document.querySelectorAll(".alt-ingredient");

                        // Toggle visibility for each alternative ingredient
                        altIngredients.forEach(ingredient => {
                            if (ingredient.style.display === "none" || ingredient.style.display === "") {
                                ingredient.style.display = "inline"; // Show alternative ingredient
                            } else {
                                ingredient.style.display = "none"; // Hide alternative ingredient
                            }
                        });

                        // Change button text based on the current state
                        const button = document.getElementById("toggle-alt-ingredients");
                        if (button.textContent.includes("Show")) {
                            button.innerHTML = "Hide Alternative Ingredients &#9650;";
                        } else {
                            button.innerHTML = "Show Alternative Ingredients &#9660;";
                        }
                    });
                </script>




                <h3>Instructions</h3>
                <div class="list-box">
                    <ol class="rounded-list">
                        <?php foreach ($instructions as $instruction) { ?>
                            <li><?php echo $instruction['step_description']; ?></li>
                        <?php } ?>
                    </ol>
                </div>


                <h3>Nutritional Info</h3>
                <div class="list-box">
                    <ol class="rounded-list">
                        <?php foreach ($nutriInfo as $info) { ?>
                            <li><?php echo $info['nutrition_text']; ?></li>
                        <?php } ?>
                    </ol>
                </div>

                <button class="button-success" onclick="window.location.href='ratings.php?meal_id=<?php echo $meal_id; ?>'">
                    <i class="fa-solid fa-star" style="color: #FDCC0D;"></i> Rate this Meal
                </button>

                <div class="comments-box">
                    <h3>Comments</h3>
                    <ul class="comments-list">
                        <?php if (count($comments) > 0): ?>
                            <?php foreach ($comments as $comment): ?>
                                <li class="comment-item">
                                    <div class="comment-header">
                                        <div class="comment-text-wrapper">
                                            <p class="comment-text">
                                                <strong><?php echo $comment['user_name']; ?>:</strong>
                                                <?php echo $comment['comment_text']; ?>
                                            </p>
                                            <p class="comment-info"><?php echo $comment['created_at']; ?></p>
                                        </div>
                                        <form method="post" action="" class="delete-form">
                                            <input type="hidden" name="delete_comment" value="<?php echo $comment['comment_id']; ?>">
                                            <button type="submit" class="delete-comment-btn">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No comments available.</p>
                        <?php endif; ?>
                    </ul>


                    <!-- Your comment form goes here -->
                    <?php if ($allowComments): ?>
                        <form method="post" action="">
                            <textarea name="comment" placeholder="Write a comment..." id="comment" rows="3" required></textarea>
                            <button type="submit" class="submit-comment-btn"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    <?php else: ?>
                        <p>Login to post comments.</p>
                    <?php endif; ?>
                    </ul>
                </div>

                <script>
                    function toggleCommentForm() {
                        const commentForm = document.querySelector('.comment-form');
                        commentForm.style.display = commentForm.style.display === 'none' ? 'block' : 'none';
                    }
                </script>
    </div>
</body>

</html>