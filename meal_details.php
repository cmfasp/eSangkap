<?php
require("0conn.php");

// Check if meal_id is set in the URL
if (isset($_GET['meal_id'])) {
    // Retrieve meal_id from the URL
    $selectedMealId = $_GET['meal_id'];

    // Fetch meal details from the database based on meal_id
    $sqlMeal = "SELECT * FROM meals WHERE meal_id = $selectedMealId";
    $resultMeal = $conn->query($sqlMeal);

    // Check if the meal is found
    if ($resultMeal && $resultMeal->num_rows > 0) {
        $mealDetails = $resultMeal->fetch_assoc();

        // Fetch description for the meal
        $mealDescription = $mealDetails['description'];

        // Fetch ingredients for the meal
        $sqlIngredients = "SELECT ingredient_name FROM ingredients WHERE meal_id = $selectedMealId";
        $resultIngredients = $conn->query($sqlIngredients);
        $ingredients = ($resultIngredients) ? $resultIngredients->fetch_all(MYSQLI_ASSOC) : [];

        // Fetch instructions for the meal
        $sqlInstructions = "SELECT step_number, step_description FROM instructions WHERE meal_id = $selectedMealId ORDER BY step_number";
        $resultInstructions = $conn->query($sqlInstructions);
        $instructions = ($resultInstructions) ? $resultInstructions->fetch_all(MYSQLI_ASSOC) : [];

        // Fetch images for the meal
        $sqlImages = "SELECT image_link FROM meal_images WHERE meal_id = $selectedMealId";
        $resultImages = $conn->query($sqlImages);
        $images = ($resultImages) ? $resultImages->fetch_all(MYSQLI_ASSOC) : [];
    } else {
        // Handle the case when the meal is not found
        $mealDetails = null;
        $mealDescription = '';
        $ingredients = [];
        $instructions = [];
        $images = [];
    }
} else {
    // Handle the case when meal_id is not set in the URL
    $mealDetails = null;
    $mealDescription = '';
    $ingredients = [];
    $instructions = [];
    $images = [];
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
            width: 100%;
            margin-top: 120px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        img {
            width: 35%;
            height: 45vh;
        } 
        .button-secondary {
            margin-top: 20px;
                margin-left: 25px;
                margin-bottom: 20px;
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
        <a href="12user_profile.php">Profile</a>
        <a href="view_categories.php"<?php echo (basename($_SERVER['PHP_SELF']) == 'meal_details.php') ? 'class="active"' : ''; ?>><i class="fa fa-fw fa-user"></i>Categories</a>
        <a href="9customer.php"><i class="fa-solid fa-utensils"></i>User Recipes</a>
        <a href="14chat.php"><i class="fa-solid fa-comment"></i>Chat</a>
        <a href="4logout.php"><i class="fas fa-fw fa-sign-out"></i> Logout</a>
    </div>

    <div class="container">
    <button class="button-secondary" onclick="window.location.href='view_categories.php'">
        <i class="fas fa-arrow-left"></i> </button>
        <div class="card">
            <div class="card-header">
                <h2><?php echo $mealDetails['meal_name']; ?></h2>
            </div>
            <div class="card-body">
                <p class="card-text"><?php echo $mealDescription; ?></p>
                <h3 class="card-title">Images</h3>
                <?php foreach ($images as $image) { ?>
                    <img src="<?php echo $image['image_link']; ?>" alt="Meal Image">
                <?php } ?>
                <p class="card-text"><a href="<?php echo $mealDetails['video_link']; ?>" target="_blank"><?php echo $mealDetails['video_link']; ?></a></p>

                <h3 class="card-title">Instructions</h3>
                <ol class="card-text">
                    <?php foreach ($instructions as $instruction) { ?>
                        <li><?php echo $instruction['step_description']; ?></li>
                    <?php } ?>
                </ol>

                <h3 class="card-title">Ingredients</h3>
                <ul class="card-text">
                    <?php foreach ($ingredients as $ingredient) { ?>
                        <li><?php echo $ingredient['ingredient_name']; ?></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
