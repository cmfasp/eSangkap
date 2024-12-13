<?php
session_start();
require("0conn.php");

if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("Location: 3login.php");
    exit();
}

$loggedInUsername = isset($_SESSION["username"]) ? $_SESSION["username"] : "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if (isset($_GET['meal_id'])) {
    $meal_id = $_GET['meal_id'];

    $stmt = $pdo->prepare("SELECT * FROM meals WHERE meal_id = ?");
    $stmt->execute([$meal_id]);
    $meal = $stmt->fetch(PDO::FETCH_ASSOC);

    $instructionsStmt = $pdo->prepare("SELECT * FROM instructions WHERE meal_id = ? ORDER BY step_number");
    $instructionsStmt->execute([$meal_id]);
    $instructions = $instructionsStmt->fetchAll(PDO::FETCH_ASSOC);

    $ingredientsStmt = $pdo->prepare("SELECT * FROM ingredients WHERE meal_id = ?");
    $ingredientsStmt->execute([$meal_id]);
    $ingredients = $ingredientsStmt->fetchAll(PDO::FETCH_ASSOC);

    $commentsStmt = $pdo->prepare("SELECT * FROM comments WHERE meal_id = ? ORDER BY created_at DESC");
    $commentsStmt->execute([$meal_id]);
    $comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

    $imagesStmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ?");
    $imagesStmt->execute([$meal_id]);
    $images = $imagesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Increment the views when the meal is viewed
    $incrementViewsStmt = $pdo->prepare("UPDATE meals SET views = views + 1 WHERE meal_id = ?");
    $incrementViewsStmt->execute([$meal_id]);

    // Fetch meal data after incrementing views
    $stmt = $pdo->prepare("SELECT * FROM meals WHERE meal_id = ?");
    $stmt->execute([$meal_id]);
    $meal = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    header("Location: admin_view_posts.php");
    exit();
}

$userLoggedIn = isset($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
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
            margin-top: 5px;
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

        .container {
            width: 100%;
            margin-top: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            background-color: white;
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
        

        .comments-box {
            padding: 20px;
            margin: 20px ;
            width: 95%;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        img {
            width: 35%;
            height: 45vh;
        }

        ol, ul {
            margin-top: 10px;
        }

        li {
            margin-bottom: 5px;
        }

        a {
            color: #007BFF;
        }
        h1, h2, h3 {
        color: #04AA6D;
         }

        .comments-list {
            list-style-type: none;
            padding: 0;
            align-items: center;
        }

        .comment-item {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
            text-align: left; 
        }

        .comment-text {
            margin-top: 5px;
        }

        .comment-info {
            font-size: 14px;
            color: #555;
        }


        form {
            margin-top: 20px;
            width: 100%;
            display: flex;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        button {
            background-color: #16b978;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            margin-top: 10px; 
            margin-left: 10px;
        }

        .add-comment-link {
            color: #16b978;
            cursor: pointer;
            text-decoration: underline;
            margin-top: 10px;
        }
        .button-secondary {
            margin-top: 40px;
            margin-left: 25px;
            color: gray;
            padding: 8px 16px;
            text-decoration: none;
            display: flex;
            align-items: center;
            border: none;
            font-size: 20px;
            background-color: transparent; 
        }
        .topnav a.active {
            background-color: lightgray;
            color: black;
        }
        .views{
            color: gray;
            font-size: 15px;
        }

 
 /* Existing CSS styles remain unchanged */



/* Updated styles for centering and adjusting width */
.meal-details-box {
    background-color: #fff;
    margin: 80px auto; /* Adjust the 'auto' margin to center the box horizontally */
    width: 60%; /* Adjust the width as needed */
    text-align: left; /* Keep the text aligned to the left */
    padding: 20px;
    box-sizing: border-box; /* Include padding and border in the box sizing */
    margin-top: 150px;
}

ol, ul {
    margin-top: 10px;
    padding-left: 20px; 
}

li {
    margin-bottom: 5px;
    list-style-position: inside; 
}

.image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            justify-content: center;
            align-items: center;
        }

        .image-grid img {
            width: 100%;
            height: 300px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
    </style>
    <title>Admin View Details</title>
</head>

<body>
    <div class="logo-container">
        <div class="logo">
            <img src="logo.png" alt="Tastebud Logo">
            <h1>Tastebud</h1>
        </div>
    </div>
    
    <div class="topnav">
        <a href="adminViewPost.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'adminViewPost.php') ? 'class="active"' : ''; ?>>Home</a>
        <a href="adminprofile.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'adminprofile.php') ? 'class="active"' : ''; ?>>Admin Profile</a>
        <a href="view_categories.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'view_categories.php') ? 'class="active"' : ''; ?>>Categories</a>
        <a href="5admin.php" <?php echo (basename($_SERVER['PHP_SELF']) == '5admin.php') ? 'class="active"' : ''; ?>>Manage Recipe</a>
        <a href="4logout.php" <?php echo (basename($_SERVER['PHP_SELF']) == '4logout.php') ? 'class="active"' : ''; ?>>Logout</a>
    </div>
    <div class="meal-details-box">
    <h1><?php echo $meal['meal_name']; ?></h1>
    <p><strong>Posted by:</strong> <?php echo $meal['username']; ?></p>
    <p>Description: <?php echo ($meal['description']); ?></p>
    <p class="views">Views: <?php echo $meal['views']; ?></p>
    <br><br>
    <h3>Images</h3>
        <div class="image-grid">
            <?php foreach ($images as $image): ?>
                <img src="<?php echo $image['image_link']; ?>" alt="Recipe Image">
            <?php endforeach; ?>
        </div>

    <a href="<?php echo $meal['video_link']; ?>" target="_blank">Watch Video</a>
    <h3>Instructions</h3>
    <ol>
        <?php foreach ($instructions as $instruction): ?>
            <li><?php echo $instruction['step_description']; ?></li>
        <?php endforeach; ?>
    </ol>

    <h3>Ingredients</h3>
    <ul>
        <?php foreach ($ingredients as $ingredient): ?>
            <li><?php echo $ingredient['ingredient_name']; ?></li>
        <?php endforeach; ?>
    </ul>
</div>


        <h3>Comments</h3>
        <div class="comments-box">
            <ul class="comments-list">
                <?php if (count($comments) > 0): ?>
                    <?php foreach ($comments as $comment): ?>
                        <li class="comment-item">
                            <p class="comment-text"><strong><?php echo $comment['user_name']; ?>:</strong> <?php echo $comment['comment_text']; ?></p>
                            <p class="comment-info"><?php echo $comment['created_at']; ?></p>

                            <!-- Add delete comment form -->
                            <?php if ($userLoggedIn): ?>
                                <form method="post" action="">
                                    <input type="hidden" name="delete_comment" value="<?php echo $comment['comment_id']; ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No comments available.</p>
                <?php endif; ?>

                <!-- Your comment form goes here -->
                <?php if ($userLoggedIn): ?>
                    <form method="post" action="">
                        <textarea name="comment" placeholder="Write a comment..." id="comment" rows="3" required></textarea>
                        <button type="submit">Submit</button>
                    </form>
                <?php else: ?>
                    <p>Login to post comments.</p>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>
