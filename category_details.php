<?php
require("0conn.php");

// Check if category_id is set in the URL
if (isset($_GET['category_id'])) {
    // Retrieve category_id from the URL
    $selectedCategoryId = $_GET['category_id'];

    // Fetch category details from the database based on category_id
    $sqlCategory = "SELECT * FROM categories WHERE category_id = $selectedCategoryId";
    $resultCategory = $conn->query($sqlCategory);

    // Check if the category is found
    if ($resultCategory && $resultCategory->num_rows > 0) {
        $categoryDetails = $resultCategory->fetch_assoc();

        // Fetch meals associated with the category
        $sqlMeals = "SELECT * FROM meals WHERE category_id = $selectedCategoryId";
        $resultMeals = $conn->query($sqlMeals); 

        // Check if meals are found
        if ($resultMeals && $resultMeals->num_rows > 0) {
            $meals = $resultMeals->fetch_all(MYSQLI_ASSOC);
        } else {
            // Handle the case when no meals are found for the category
            $meals = [];
        }
    } else {
        // Handle the case when the category is not found
        $categoryDetails = null;
        $meals = [];
    }
} else {
    // Handle the case when category_id is not set in the URL
    $categoryDetails = null;
    $meals = [];
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

        .button-primary {
            background-color: #16b978;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .button-primary:hover {
            background-color: #128a61;
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
            width: 100%;
            min-height: 100vh;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex-direction: column;
            overflow-y: auto;
        }

        h3 {
            color: #16b978;
            font-size: 23px;
            text-align: center;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        ul li {
            margin: 10px 0;
        }

        .meals-container {
            border: none;
            border-radius: 5px;
            max-width: 100%;
            box-sizing: border-box;
        }

        .h3 {
            margin-bottom: 0; /* Add this line to remove margin-bottom */
        }

        .recipe-box {
            box-sizing: border-box;
            float: left;
            padding: 10px;
            border-radius: 15px;
            background: white;
            margin: 10px;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
            width: calc(33.33% - 20px); /* Adjusted to 33.33% minus margin */
        }
        .recipe-box img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 10px;
        }

        .meal-name {
            font-size: 18px;
            display: block;
            margin: 10px 0;
            color: #16b978;
            text-decoration: none;
        }

        .meal-username {
            font-size: 17x;
            color: Black; /* Choose your desired color */
            margin-top: 5px; /* Adjust as needed */
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

        .button-secondary {
            margin-top: 145px;
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
        .meal-name a {
        text-decoration: none; /* Remove underline for meal name */
        color: #16b978;
    }
    .view-details-button {
        padding: 8px 16px;
        background-color: #04AA6D;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none; /* Remove underline */
        font-size: 14px;
        margin-top: 5px;
        display: inline-block; /* Add this line to make it a block element */
        margin-bottom: 10px;
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
        <a href="12user_profile.php"><i class="fa fa-fw fa-user"></i>Profile</a>
        <a href="view_categories.php"<?php echo (basename($_SERVER['PHP_SELF']) == 'category_details.php') ? 'class="active"' : ''; ?>><i class="fa-solid fa-list"></i>Categories</a>
        <a href="9customer.php"><i class="fa-solid fa-utensils"></i>User Recipes</a>
        <a href="14chat.php"><i class="fa-solid fa-comment"></i>Chat</a>
        <a href="4logout.php"><i class="fas fa-fw fa-sign-out"></i> Logout</a>
    </div>
    <div class="container">
        <button class="button-secondary" onclick="window.location.href='view_categories.php'">
            <i class="fas fa-arrow-left"></i> </button>
        <?php if ($categoryDetails): ?>
            <h3><?php echo $categoryDetails['category_name']; ?></h3>

            <div class="meals-container">
            <?php if (!empty($meals)): ?>
        <?php foreach ($meals as $meal): ?>
            <div class="recipe-box">
                <?php
                // Fetch the first image associated with the meal
                $meal_id = $meal['meal_id'];
                $imageStmt = $conn->prepare("SELECT * FROM meal_images WHERE meal_id = ? LIMIT 1");
                $imageStmt->bind_param('i', $meal_id);
                $imageStmt->execute();
                $resultImage = $imageStmt->get_result();
                $firstImage = $resultImage->fetch_assoc();

                if ($firstImage) {
                    echo '<img src="' . $firstImage['image_link'] . '" style="max-width: 100%; border-radius: 10px;">';
                }
                ?>
                <h2 class="meal-name">
                    <a href="meal_details.php?meal_id=<?php echo $meal['meal_id']; ?>">
                        <?php echo $meal['meal_name']; ?>
                    </a>
                </h2>
                <!-- Display username below the meal name -->
                <p><b class="meal-username"><?php echo $meal['username']; ?></b></p>
                <p><b>Description: </b><?php echo nl2br($meal['description']); ?></p>
                <p class="views">Views: <?php echo $meal['views']; ?></p>
                <p>Date: <?php echo date('M j, Y', strtotime($meal['date_created'])); ?></p>
                <a class="view-details-button" href="meal_details.php?meal_id=<?php echo $meal['meal_id']; ?>">View More</a>
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
