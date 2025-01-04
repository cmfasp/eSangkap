<?php
session_start();
date_default_timezone_set('Asia/Manila');
require("0conn.php");

// Check if the user is logged in and get the username from the session
if (!isset($_SESSION['username'])) {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username']; // Get the username from the session

if (isset($_GET['category_id'])) {
    $selectedCategoryId = $_GET['category_id'];
    $sqlCategory = "SELECT * FROM categories WHERE category_id = $selectedCategoryId";
    $resultCategory = $conn->query($sqlCategory);
    if ($resultCategory && $resultCategory->num_rows > 0) {
        $categoryDetails = $resultCategory->fetch_assoc();

        $sqlMeals = "SELECT * FROM meals WHERE category_id = $selectedCategoryId";
        $resultMeals = $conn->query($sqlMeals); 
        if ($resultMeals && $resultMeals->num_rows > 0) {
            $meals = $resultMeals->fetch_all(MYSQLI_ASSOC);
        } else {
            $meals = [];
        }
    } else {
        $categoryDetails = null;
        $meals = [];
    }
} else {
    $categoryDetails = null;
    $meals = [];
}

// Function to check if a meal is already in the user's favorites
function isFavorite($mealId, $username) {
    global $conn;
    $sql = "SELECT * FROM favorites WHERE meal_id = ? AND username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $mealId, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
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
            ;
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
     
        .meal-name a {
            text-decoration: none;
            color: black;
            font-size: 20px;
            font-weight: bold;
            font-family: 'Poppins', sans-serif;
        }

        .meal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    .view-details-button {
        display: inline-block;
            text-decoration: none;
            padding: 8px 16px;
            background-color: darkred;
            color: #fff;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
    }
    .favorite-button {
            background-color: #ffcccb;
            color: darkred;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            padding: 8px 10px;
            border-radius: 25px;
            border: none;
        }

        .favorite-button.added {
            background-color: darkred;
            color: white;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            padding: 8px 10px;
            border-radius: 25px;
            box-shadow: none;
            border: none;
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
        <a href="view_categories.php"class="active"><i class="fa-solid fa-list"></i>Categories</a>
        <a href="12user_profile.php"><i class="fas fa-user"></i>Profile</a>
        <a href="4logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </div>
    <div class="container">
        <?php if ($categoryDetails): ?>
            <h1><?php echo $categoryDetails['category_name']; ?></h1>

            <div class="meals-container">
                <?php if (!empty($meals)): ?>
                    <?php foreach ($meals as $meal): ?>
                        <div class="recipe-box">
                            <?php
                            $meal_id = $meal['meal_id'];
                            $isFavorite = isFavorite($meal_id, $username);
                            $imageStmt = $conn->prepare("SELECT * FROM meal_images WHERE meal_id = ? LIMIT 1");
                            $imageStmt->bind_param('i', $meal_id);
                            $imageStmt->execute();
                            $resultImage = $imageStmt->get_result();
                            $firstImage = $resultImage->fetch_assoc();

                            if ($firstImage) {
                                echo '<img src="' . $firstImage['image_link'] . '" style="max-width: 100%; border-radius: 10px;">';
                            }
                            ?>
                            <div class="meal-header">
                                <h2 class="meal-name">
                                    <a href="meal_details.php?meal_id=<?php echo $meal['meal_id']; ?>">
                                        <?php echo $meal['meal_name']; ?>
                                    </a>
                                </h2>
                                <a href="add_to_favorites.php?meal_id=<?php echo $meal_id; ?>" 
                                class="favorite-button <?php echo $isFavorite ? 'added' : ''; ?>">
                                <i class="fas fa-heart"></i> 
                                <?php echo $isFavorite ? 'Added to Favorites' : 'Favorite'; ?>
                                </a>
                            </div>
                            <p><b class="meal-username"><?php echo $meal['username']; ?></b></p>
                            <p><b>Description: </b><?php echo nl2br($meal['description']); ?></p>
                            <p class="views">Views: <?php echo $meal['views']; ?></p>
                            <p>Date: <?php echo getTimeElapsedString($meal['date_created']); ?></p>
                            <a class="view-details-button" href="11meal_details_comments.php?meal_id=<?php echo $meal['meal_id']; ?>">View More</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No meals found for this category.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p>Category not found.</p>
        <?php endif; ?>
    </div>
</body>

</html>