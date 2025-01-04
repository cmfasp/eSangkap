<?php
session_start();
date_default_timezone_set('Asia/Manila');
require("0conn.php");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    $stmt = $pdo->prepare("SELECT * FROM meals WHERE username = ? ORDER BY date_created DESC");
    $stmt->execute([$username]);
    $userRecipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: 1registration.php");
    exit();
}

function getCategoryName($pdo, $category_id)
{
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    return $category ? $category['category_name'] : 'Unknown';
}

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
    <title>User Profile</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
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
            text-align: left;
            padding-bottom: 20px;
            display: flex;
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

        .logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .title {
            color: #f04e23;
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

        .buttons-container {
            display: flex;
            gap: 12px;
        }

        .action-button {
            padding: 8px 16px;
            background-color: #f04e23;
            color: #fff;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .action-button i {
            margin-right: 10px;
        }

        .action-button:hover {
            background-color: #ffcccb;
            color: darkred;
        }

        .recipe-box {
            width: calc(70% - 80px);
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            
        }

        .recipe-box img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }


        .recipe-details p {
            font-size: 17px;
            color: black;
        }

        .timeline-section {
            max-width: 800px
        }

        .content {
            max-width: 900px;
            margin-left: 400px;
            margin-right: auto;
        }

        .add-recipe-container {
            background-color: white;
            padding: 10px;
            box-sizing: border-box;
            margin-bottom: 10px;
            border-bottom: solid 3px whitesmoke;
            margin-top: 40px;
            align-items: left;
            justify-content: left;
        }

        .add-recipe-button {
            color: black;
            padding: 10px 50px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            margin-bottom: 20px;
            margin-left: 10px;
            transition: background-color 0.3s;
            margin-right: 20px;
        }

        .add-recipe-button:hover {
            background-color: whitesmoke;
            color: black;

        }

        .add-recipe-button i {
            margin-right: 10px;
        }

        .recipe-box {
            background-color: #fff;
            box-sizing: border-box;
            border-bottom: 3px;
            background: white;
            margin-bottom: 5px;
            margin-top: 25px;
            width: calc(180% - 100px);
            margin-bottom: 25px;
            margin-right: 100px;
        }

        .recipe-box img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }

        .recipe-details h2 {
            margin: 0;
            font-size: 20px;
            color: #333;
            display: inline-block;
        }

        .recipe-list {
            list-style: none;
            padding: 0;
            margin: 0;
            justify-content: space-between;
            margin-left: 300px;
        }

        h1 {
            color: black;
            margin-top: 140px;
        }

        h3 {
            color: #f04e23;
            margin-left: 2px;
        }

        h2 {
            color: black;
            margin-left: 2px;
            display: inline-block;
        }


        .favorite-button {
            float: right;
            background-color: #ffcccb;
            color: darkred;
            padding: 6px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 14px;
        }

        .favorite-button:hover {
            background-color: darkred;
            color: white;
        }


        .header-image {
            position: relative;
            margin-top: 20px;
        }

        .header-image img {
            width: 155%;
            border-radius: 10px;
            height: 300px;
        }

        .header-image h1 {
            position: absolute;
            top: 0%;
            left: 40%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 23px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);
            text-align: left;
        }

        .view-details-button {
            padding: 8px 16px;
            background-color: darkred;
            color: #fff;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            margin-top: 10px;
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
        <a href="view_categories.php"><i class="fa-solid fa-list"></i>Categories</a>
        <a href="12user_profile.php" class="active"><i class="fas fa-user"></i>Profile</a>
        <a href="4logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </div>

    <div class="content">
        <h1>Hi <?php echo htmlspecialchars($username); ?>!</h1>
        <div class="header-image">
            <img src="dishes.jpg" alt="Create Your Own Dish">
            <h1>Get creative and share your cooking! Upload or share your recipe details, and let’s celebrate delicious food together!</h1>
        </div>
        <div class="timeline-section">
            <h2>Timeline</h2>
            <div class="buttons-container">
                <a href="13add_recipe.php" class="action-button">
                    <i class="fa-solid fa-pen-to-square"></i> Create your post
                </a>
                <a href="testimony.php" class="action-button">
                    <i class="fa-solid fa-comment"></i> Write Testimonies
                </a>
            </div>
            <ul class="recipe-list">
                <?php foreach ($userRecipes as $recipe) { ?>
                    <li>
                        <div class="recipe-box">
                            <?php
                            $meal_id = $recipe['meal_id'];
                            $imageStmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ? LIMIT 1");
                            $imageStmt->execute([$meal_id]);
                            $firstImage = $imageStmt->fetch(PDO::FETCH_ASSOC);
                            if ($firstImage) {
                                echo '<img src="' . htmlspecialchars($firstImage['image_link']) . '" alt="Recipe Image">';
                            }
                            ?>
                            <div class="recipe-details">
                                <h2><?php echo htmlspecialchars($recipe['meal_name']); ?></h2>
                                <a href="add_to_favorites.php?meal_id=<?php echo $meal_id; ?>" class="favorite-button">
                                    <i class="fas fa-heart"></i> Favorite
                                </a>
                                <p class="username"><?php echo htmlspecialchars($recipe['username']); ?></p>
                                <p><b>Description:</b> <?php echo htmlspecialchars(strlen($recipe['description']) > 100 ? substr($recipe['description'], 0, 100) . '...' : $recipe['description']); ?></p>
                                <p>Views: <?php echo htmlspecialchars($recipe['views']); ?></p>
                                <p>Date: <?php echo getTimeElapsedString($recipe['date_created']); ?></p>
                                <a class="view-details-button" href="15userposts.php?meal_id=<?php echo $meal_id; ?>">Show more</a>
                            </div>

                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</body>

</html>