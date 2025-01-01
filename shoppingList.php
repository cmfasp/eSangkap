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

    // Fetch ingredients
    $ingredientsStmt = $pdo->prepare("SELECT * FROM ingredients WHERE meal_id = ?");
    $ingredientsStmt->execute([$meal_id]);
    $ingredients = $ingredientsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all images associated with the meal_id from meal_images table
    $imagesStmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ?");
    $imagesStmt->execute([$meal_id]);
    $images = $imagesStmt->fetchAll(PDO::FETCH_ASSOC);

    $nutriInfoStmt = $pdo->prepare("SELECT * FROM nutritional_info WHERE meal_id = ?");
    $nutriInfoStmt->execute([$meal_id]);
    $nutriInfo = $nutriInfoStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: 9customer.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>eSangkap</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            background-color: #ffffff;
            /* Set to white */
        }

        .logo-container {
            padding: 7px;
            position: fixed;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .logo-container img {
            height: 60px;
            width: auto;
            margin-right: 10px;
            border-radius: 50%;
        }

        .logo-title {
            color: #f04e23;
            font-size: 1.8rem;
        }

        .sidebar {
            background-color: #f04e23;
            height: 100%;
            width: 250px;
            position: fixed;
            top: 70px;
            left: 0;
            overflow-x: hidden;
            padding-top: 20px;
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

        .sidebar a i {
            margin-right: 15px;
        }

        .sidebar a.active {
            background-color: #ffcccb;
            color: darkred;
        }

        .sidebar a:hover {
            background-color: white;
            color: darkred;
        }

        .container {
            margin: 0 auto;
            padding: 20px;
            max-width: 1100px;
            /* Increased width for wider content */
            background-color: #fff;
            margin-top: 100px;
            margin-left: 380px;
        }


        h1 {
            color: darkred;
        }

        h2 {
            color: #333;
            margin-top: 10px;
        }

        h3 {
            margin-top: 30px;
        }

        .ingredient-list {
            list-style: none;
            padding: 0;
            margin-top: 20px;
        }

        .ingredient-list li {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            width: 95%;
            /* Utilizes more space in the container */
        }

        .ingredient-list li:hover {
            background-color: rgb(224, 224, 224);
            color: black;
        }

        .ingredient-list input {
            margin-right: 15px;
            width: 20px;
            height: 20px;
            border-radius: 3px;
            border: 2px solid #f04e23;
        }

        .ingredient-list input:checked {
            background-color: #f04e23;
            border-color: #f04e23;
        }

        .ingredient-list label {
            cursor: pointer;
        }

        .ingredient-list input:checked+label {
            text-decoration: line-through;
            color: darkred;
        }

        .bought-ingredients {
            margin-top: 40px;
        }

        .bought-ingredients ul {
            list-style: none;
            padding: 0;
        }

        .bought-ingredients li {
            margin: 5px 0;
            color: darkgreen;
            background-color: rgb(192, 255, 206);
            padding: 10px;
            border-radius: 5px;
        }

        .meal-image {
            display: block;
            margin: 0 auto;
            width: 150%;
            border-radius: 10px;
            height: 400px;
            object-fit: cover;
            /* Ensures the image fills the width without stretching */
            border-radius: 15px;
            margin-top: 20px;
        }

        .meal-images-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .meal-images-container img {
            margin: 10px;
            width: 200px;
            height: 200px;
            object-fit: cover;
        }

        .no-ingredients {
            text-align: center;
            font-size: 1.5rem;
            color: darkred;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="logo-container">
        <img src="logo.jpg" alt="Logo">
        <h2 class="logo-title">eSangkap</h2>
    </div>
    <div class="sidebar">
        <a href="9customer.php"><i class="fa fa-fw fa-home"></i> Home</a>
        <a href="favoritesreen.php"><i class="fas fa-heart"></i> Favorites</a>
        <a href="view_categories.php"><i class="fas fa-list"></i> Categories</a>
        <a href="12user_profile.php" class="active"><i class="fas fa-user"></i> Profile</a>
        <a href="about_us.php"><i class="fas fa-info-circle"></i> About Us</a>
        <a href="4logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="container">
        <h1>Shopping List</h1>

        <img src="<?php echo $images[0]['image_link']; ?>" alt="Meal Image" class="meal-image">
        <h2><?php echo $meal['meal_name']; ?></h2>
        <h3>Ingredients</h3>
        <ul class="ingredient-list">
            <?php if (count($ingredients) > 0) { ?>
                <?php foreach ($ingredients as $ingredient) { ?>
                    <li>
                        <input type="checkbox" id="ingredient_<?php echo $ingredient['ingredient_id']; ?>">
                        <label for="ingredient_<?php echo $ingredient['ingredient_id']; ?>"><?php echo $ingredient['ingredient_name']; ?></label>
                    </li>
                <?php } ?>
            <?php } else { ?>
                <div class="no-ingredients">You completed all the ingredients!</div>
            <?php } ?>
        </ul>
        <div style="border-radius: 10px; background-color:rgb(231, 231, 231); border: #555 solid 1px; padding-left: 20px; padding-right:50px; padding-top:5px; padding-bottom:5px;">
            <p style="font-size: 16px; margin: 0px;"><b>Where to buy this ingredients?</b></p>
            <p style="font-size: 12px; margin: 0px;"><?= $meal['where_buy'] ?></p>
        </div>

        <div class="bought-ingredients">
            <h3>Bought Ingredients</h3>
            <ul id="boughtIngredients"></ul>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');
            var boughtIngredientsList = document.getElementById('boughtIngredients');
            var noIngredientsMessage = document.querySelector('.no-ingredients');

            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        addToBoughtIngredients(this.nextElementSibling.textContent, this.id);
                        this.parentNode.remove(); // Remove the ingredient from the checklist
                    }
                });
            });

            function addToBoughtIngredients(ingredientName, ingredientId) {
                // Check if the ingredient is already in the bought list
                if (!boughtIngredientsList.querySelector(`[data-id="${ingredientId}"]`)) {
                    var listItem = document.createElement('li');
                    listItem.textContent = ingredientName;
                    listItem.setAttribute('data-id', ingredientId); // Add a unique identifier
                    boughtIngredientsList.appendChild(listItem);
                    updateNoIngredientsMessage();
                }
            }

            function updateNoIngredientsMessage() {
                if (boughtIngredientsList.children.length > 0) {
                    noIngredientsMessage.style.display = 'none';
                } else {
                    noIngredientsMessage.style.display = 'block';
                }
            }
        });
    </script>

</body>

</html>