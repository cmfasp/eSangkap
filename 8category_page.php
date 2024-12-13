<?php
session_start();
require("0conn.php");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
if (isset($_GET["category_id"])) {
    $category_id = $_GET["category_id"];

    
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($category) {
        $category_name = $category["category_name"];
    } else {
      
        $category_name = "Category Not Found";
    }
} else {
    
    $category_name = "Category Not Selected";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Page</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f3f3;
        }

    

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            margin-top: 170px;
        }

        h2, h3, p {
            margin: 10px 0;
            font-weight: bold;
        }


        .btn-secondary {
            background-color: #4caf50;
            color: #fff;
        }

        .recipe-list {
            margin-top: 20px;
            font-size: 20px;
        }

        .mb-3 {
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #4caf50;
            color: #FFF;
            font-weight: bold;
            text-align: center;
        }

        .list-group {
            background-color: #f8f9fa; 
        }

        .list-group-item {
            border-radius: 5px;
            margin-bottom: 10px;
            color: #000;
            background-color:  rgba(76, 175, 80, 0.2); 
            border: 2px solid #4caf50; 
    

        }
        .category-details{

            font-size: 70;
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
            height: 90px;
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
            z-index: 1; /* Set a lower z-index */
        }

        .topnav a {
            color: #f2f2f2;
            text-align: center;
            padding: 15px 25px;
            text-decoration: none;
            font-size: 17px;
            display: inline-block;
        }

        .topnav a:hover {
            background-color: #04AA6D;
            color: white;
        }

        .topnav a.active {
            background-color: #04AA6D;
            color: white;
        }

        .topnav a i {
            margin-right: 10px;
        }

        
        .navbar-toggler-icon {
            background-color: #16b978; /* Green color for the toggle icon */
        }

        .logo {
            display: flex;
            align-items: center;
        }
        .logo h1{
            color: #16b978;
            
            font-size: 25px;
            justify-content: center;

        }

        .logo img {
            width: 40px; /* Adjust the width as needed */
            margin-right: 10px;
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
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="nav-link" href="adminViewPost.php">
            <i class="fa fa-fw fa-home"></i>Home
        </a>
        <a class="nav-link" href="adminprofile.php">
            <i class="fas fa-fw fa-user"></i>Admin Profile
        </a>
        <a class="nav-link" href="5admin.php">
            <i class="fa-solid fa-utensils"></i>Manage Recipe
        </a>
        <a class="nav-link" href="4logout.php">
            <i class="fas fa-fw fa-sign-out"></i>Logout
        </a>
    </div>

    <div class="container">
        <div class="card-header">
            <h2 class="mb-3"> <?php echo $category_name; ?></h2>
        </div>
       

        <div class="recipe-list">
            <p class="mb-3">Recipes</p>
            
            <?php
            $stmt = $pdo->prepare("SELECT * FROM meals WHERE category_id = ?");
            $stmt->execute([$category_id]);
            $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($recipes) > 0) {
                echo '<div class="list-group">';
                foreach ($recipes as $recipe) {
                    echo '<a href="7recipe_details.php?recipe_id=' . $recipe['meal_id'] . '" class="list-group-item list-group-item-action">' . $recipe['meal_name'] . '</a>';
                }
                echo '</div>';
            } else {
                echo "<p>No recipes found in this category.</p>";
            }
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
