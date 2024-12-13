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

    $ingredientsStmt = $pdo->prepare("SELECT * FROM ingredients WHERE meal_id = ?");
    $ingredientsStmt->execute([$meal_id]);
    $ingredients = $ingredientsStmt->fetchAll(PDO::FETCH_ASSOC);
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
            margin-top: 20px; 
            width: 100%;
            padding-left: 50px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            flex-grow: 1;
            background-color: #fff;
            overflow-y: auto; 
        }
        h1 {
            margin-left: 614px;
            font-size: 24px;
        }

        h2{
            color: #04AA6D;
            font-size: 20px;

        }

        p {
            font-size: 16px;
            margin: 5px 0;
            margin-top: 140px;
        }

        a {
            text-decoration: none;
            color: #007BFF;
            cursor: pointer;
        }

        .ingredient-list {
            list-style: none;
            padding: 0;
        }

        .ingredient-list li {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .ingredient-list input {
            margin-right: 10px;
        }

        .bought-ingredients {
            color: #04AA6D;
            font-size: 15px;
            background-color: #fff;
            width: 100%;
            padding-left: 50px;
            flex-grow: 1;
            background-color: #fff;
            overflow-y: auto; 
            padding-bottom: 200px;

        }

        .bought-ingredients h3 {
            color: #007BFF;
        }
        .button-secondary {
            margin-top: 140px;
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
        <a href="12user_profile.php"<?php echo (basename($_SERVER['PHP_SELF']) == 'shoppingList.php') ? 'class="active"' : ''; ?>><i class="fa fa-fw fa-user"></i>Profile</a>
        <a href="view_categories.php"><i class="fas fa-fw fa-user"></i>Categories</a>
        <a href="9customer.php"><i class="fa-solid fa-utensils"></i>User Recipes</a>
        <a href="14chat.php"><i class="fa-solid fa-comment"></i>Chat</a>
        <a href="4logout.php"><i class="fas fa-fw fa-sign-out"></i> Logout</a>
    </div>


    <div class="container">
    <button class="button-secondary" onclick="window.location.href='12user_profile.php'">
        <i class="fas fa-arrow-left"></i> </button>
        <h1>Shopping List</h1>
        <h2><?php echo $meal['meal_name']; ?></h2>
        <h3>Ingredients</h3>
        <ul class="ingredient-list">
            <?php foreach ($ingredients as $ingredient) { ?>
                <li>
                    <input type="checkbox" id="ingredient_<?php echo $ingredient['ingredient_id']; ?>">
                    <label for="ingredient_<?php echo $ingredient['ingredient_id']; ?>"><?php echo $ingredient['ingredient_name']; ?></label>
                </li>
            <?php } ?>
        </ul>
        </div>

        <div class="bought-ingredients">
            <h3>Bought Ingredients</h3>
            <ul id="boughtIngredients"></ul>
</div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');
            var boughtIngredientsList = document.getElementById('boughtIngredients');

            checkboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    if (this.checked) {
                        addToBoughtIngredients(this.nextElementSibling.textContent);
                        this.parentNode.remove(); // Remove the ingredient from the list
                    }
                });
            });

            function addToBoughtIngredients(ingredientName) {
                var listItem = document.createElement('li');
                listItem.textContent = ingredientName;
                boughtIngredientsList.appendChild(listItem);
            }
        });
    </script>
</body>
</html>