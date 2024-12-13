<?php
session_start();
require("0conn.php");

if (isset($_GET['meal_id'])) {
    $meal_id = $_GET['meal_id'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }

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
    header("Location: 9customer.php");
    exit();
}

$userLoggedIn = isset($_SESSION['username']);
$allowComments = $userLoggedIn;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userLoggedIn) {
    if (isset($_POST['comment'])) {
        $comment_text = $_POST['comment'];
        $insertStmt = $pdo->prepare("INSERT INTO comments (meal_id, user_name, comment_text) VALUES (?, ?, ?)");
        $insertStmt->execute([$meal_id, $_SESSION['username'], $comment_text]);

        header("Location: 11meal_details_comments.php?meal_id=$meal_id");
        exit();
    } elseif (isset($_POST['delete_comment'])) {
        $comment_id = $_POST['delete_comment'];

        $commentStmt = $pdo->prepare("SELECT * FROM comments WHERE comment_id = ?");
        $commentStmt->execute([$comment_id]);
        $commentToDelete = $commentStmt->fetch(PDO::FETCH_ASSOC);

        if ($commentToDelete && $_SESSION['username'] === $commentToDelete['user_name']) {
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
        flex-grow: 1;
        background-color: #fff;
        width: 100%;
        padding: 20px;
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
        display: inline-block;
    }

    .delete-comment-btn,
        .submit-comment-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px; /* Add padding for better visual appearance */
        }

        .delete-comment-btn {
            margin-left: 850px;
            color: grey;
        }
        .submit-comment-btn {
            background-color: #16b978;
            color: white;
            border-radius: 5px;         
            padding: 25px; /* Adjust padding for better visual appearance */
        }


        .submit-comment-btn:hover {
            background-color: #128d63;
        }
    .views {
        font-size: 16px;
        font-family:system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        font-weight: 1000;
        border: #16b978;
        background-color: #16b978;
        color: white;
        border-radius: 20px;
        width: 8%;
        padding: 15px;
        margin-left: 739px;
        text-align: center;
    }

    .comments-list {
        list-style-type: none;
        padding: 0;
        align-items: center;
    }

    .comment-item {
        border-radius: 20px;
        margin-bottom: 10px;
        text-align: left; 
        margin-right: 624px;
        margin-left: 60px;
        background-color: #f3f3f3;
        padding-bottom: 10px;
    }

    .comment-text {
        margin-right: 20px;
        padding-right: 10px;
        margin-bottom: 10px;
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
        margin-left: 20px;
    }

    form textarea {
        width: 55%; /* Adjusted width to 60% */
        box-sizing: border-box;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ddd;
        margin-left: 60px;
    }

    .comments-box p.no-comments {
       margin-left: 30%;
       padding: 20px;
    }
   
    .add-comment-link {
        color: #16b978;
        cursor: pointer;
        text-decoration: underline;
        margin-top: 10px;
        margin-left: 10px;
        margin-right: 100px;
        display: inline-block;
        width: 55%; /* Adjusted width to 60% */
       
    }
    .button-success{
        margin-left: 60px; /* Adjusted margin to 40px */
        color: white;
        padding: 8px 16px;
        display: inline-block;
        border: none;
        font-size: 16px;
        text-align: center;
    }

    .button-secondary {
        margin-top: 140px;
        margin-left: 60px; /* Adjusted margin to 40px */
        color: grey;
        padding: 8px 16px;
        display: inline-block;
        border: none;
        font-size: 16px;
        text-align: center;
        background-color: transparent; /* Remove the green background */
        margin-bottom: -100%;
    }


    .topnav a.active {
        background-color: lightgray;
        color: black;
    }
    .watch-video {
        margin-left: 30%;
       padding: 20px;
    }
    img {
        padding: 20px;
        margin-right: 10px;
        width: 60%;
        height: 60vh;
        object-fit: cover;
        border-radius: 30px;
        margin-left: 40px;
    }
    h3 {
        font-weight: bold;
        font-family: sans-serif;
        margin-top: 30px;
        margin-left: 60px;
    }
    p{
        margin-left: 60px;
    }
    .meal-details-box {
        margin-top: 60px;
        align-items: center;
    }

    .meal-details-box h1,
    .meal-details-box p,
    .meal-details-box button {
        margin-left: 60px; /* Adjusted margin to 40px */

    }
    .list-box {
        width: 63%;
        margin-top: 20px;
        overflow: hidden;
        margin-bottom: 15px;
        margin-left: 20px;

    }

    .list-box h3 {
            background: #16b978; /* Adjust the color */
            padding: 10px 20px;
            font-size: 20px;
            font-weight: 700;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

    .list-box ul {
            position: relative;
        }

    .list-box ul li {
        list-style: none;
        padding: 10px;
        width: 100%;
        background: #fff;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.5s;
    }

    .list-box ul li:hover {
        transform: scale(1.1);
        z-index: 5;
        background: #16b978; /* Change hover color to green */
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        color: #fff;
    }

    .list-box ul li span {
        width: 20px;
        height: 20px;
        text-align: center;
        line-height: 20px;
        background: #16b978; /* Change initial color */
        color: #fff;
        display: inline-block;
        border-radius: 50%;
        margin-right: 10px;
        font-size: 12px;
        font-weight: 600;
        transform: translateY(-2px);
    }

    .list-box ul li:hover span {
        background: #fff;
        color: #16b978; /* Change hover color to green */
    }
ol.rounded-list {
            counter-reset: li;
            list-style: none;
            font: 15px 'trebuchet MS', 'lucida sans';
            padding: 0;
            width: 115%;
            

        }

        ol.rounded-list ol {
            margin: 0 0 0 2em;
        }

        ol.rounded-list a {
            position: relative;
            display: block;
            padding: .4em .4em .4em 2em;
            padding: .4em;
            margin: .5em 0;
            background: #ddd;
            color: #444;
            text-decoration: none;
            border-radius: 15px;
            transition: all .3s ease-out;
        }

        ol.rounded-list a:hover {
            background: #eee;
        }

        ol.rounded-list a:hover:before {
            transform: rotate(360deg);
        }

        ol.rounded-list a:before {
            content: counter(li);
            counter-increment: li;
            position: absolute;
            left: -1.3em;
            top: 50%;
            background: #87ceeb;
            height: 2em;
            width: 2em;
            line-height: 2em;
            border: .3em solid #fff;
            text-align: center;
            font-weight: bold;
            border-radius: 2em;
            transition: all .3s ease-out;
        }

    .rounded-list a {
        position: relative;
        display: block;
        padding: .4em .4em .4em 2em;
        padding: .4em;
        margin: .5em 0;
        background: #ddd;
        color: #444;
        text-decoration: none;
        border-radius: .3em;
        transition: all .3s ease-out;
    }

    .rounded-list a:hover {
        background: #eee;
    }

    .rounded-list a:hover:before {
        transform: rotate(360deg);
    }

    .rounded-list a:before {
        content: counter(li);
        counter-increment: li;
        position: absolute;
        left: -1.3em;
        top: 50%;
        margin-top: -1.3em;
        background: #87ceeb;
        height: 2em;
        width: 2em;
        line-height: 2em;
        border: .3em solid #fff;
        text-align: center;
        font-weight: bold;
        border-radius: 2em;
        transition: all .3s ease-out;
    }

    .list-box ol.rounded-list li,
    .instructions ol.rounded-list li {
        position: relative;
        padding: 1rem;
        background: #f3f3f3;
        border-radius: .3em;
        margin-top: 1rem;
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
        background: #16b978;
        height: 2em;
        width: 2em;
        line-height: 2em;
        border: .3em solid #fff;
        text-align: center;
        font-weight: bold;
        border-radius: 2em;
        transition: all .3s ease-out;
        color: #fff;
    }
    .watch-video {
        display: inline-block;
        padding: 10px 16px;
        background-color: #16b978;
        color: #fff;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
        margin-top: 5px;
        box-shadow: 0 5px 5px rgba(0, 0, 0, 0.2);
    }

    .watch-video:hover {
        background-color: #128a61;
        color: WHITE;
    }
    .watch-video i {
        margin-right: 8px;
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
    <a href="12user_profile.php"><i class="fas fa-fw fa-user"></i>Profile</a>
        <a href="view_categories.php"><i class="fa-solid fa-list"></i>Categories</a>
        <a href="9customer.php" <?php echo (basename($_SERVER['PHP_SELF']) == '11meal_details_comments.php') ? 'class="active"' : ''; ?>><i class="fa fa-fw fa-utensils"></i>User Recipes</a>
        <a href="14chat.php"><i class="fa-solid fa-comment"></i>Chat</a>
        <a href="4logout.php"><i class="fas fa-fw fa-sign-out"></i> Logout</a>
    </div>
    <div class="container">
    <button class="button-secondary" onclick="window.location.href='15userposts.php'">
            <i class="fas fa-arrow-left"></i>
    </button>
    <div class="meal-details-box">
    <h1><?php echo $meal['meal_name']; ?></h1>
    <h2><p><?php echo $meal['username']; ?></p></strong><h2>
</div>

    <?php foreach ($images as $image): ?>
        <img src="<?php echo $image['image_link']; ?>" alt="Meal Image">
    <?php endforeach; ?><br>
    <a class="watch-video" href="<?php echo $meal['video_link']; ?>" target="_blank">
                        <i class="fas fa-play-circle"></i> Watch Video
                    </a>
    <h3>Description: </h3>
    <p><?php echo $meal['description']; ?></p>
    <h3><p class= "views">Views: <?php echo $meal['views']; ?></p></h3>

  
    <h3>Ingredients</h3>
    <div class="list-box">
        <ol class="rounded-list">
            <?php foreach ($ingredients as $key => $ingredient): ?>
                <li>
                    <span><?php echo $key + 1; ?></span>
                    <?php echo $ingredient['ingredient_name']; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
    <h3>Instructions</h3>
<div class="list-box">
  <ol class="rounded-list">
    <?php foreach ($instructions as $key => $instruction): ?>
      <li>
        <span><?php echo $key + 1; ?></span>
        <?php echo $instruction['step_description']; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</div>

<button class="button-success" onclick="window.location.href='ratings.php?meal_id=<?php echo $meal_id; ?>'">
    <i class="fa-solid fa-star" style="color: #FDCC0D;"></i> Rate this Meal
</button>


   
        <h3>Comments</h3>
    <div class="comments-box">
        <ul class="comments-list">
            <?php if (count($comments) > 0): ?>
                <?php foreach ($comments as $comment): ?>
                    <li class="comment-item">
                        <div class="comment-header">
                        <form method="post" action="">
                                <input type="hidden" name="delete_comment" value="<?php echo $comment['comment_id']; ?>">
                                <button type="submit" class="delete-comment-btn"><i class="fas fa-trash-alt"></i></button>
                            </form>
                            <p class="comment-text">
                                <strong><?php echo $comment['user_name']; ?>:</strong> <?php echo $comment['comment_text']; ?>
            
                            </p>
                            <p class="comment-info"><?php echo $comment['created_at']; ?></p>
                        </div>
                        <?php if ($userLoggedIn && $_SESSION['username'] === $comment['user_name']): ?>
        
                        <?php endif; ?>
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
                    <!-- Use Font Awesome paper-plane icon for submit button -->
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
