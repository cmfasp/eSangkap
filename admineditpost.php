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

    $existingImagesStmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ?");
    $existingImagesStmt->execute([$meal_id]);
    $existingImages = $existingImagesStmt->fetchAll(PDO::FETCH_ASSOC);

    $instructionsStmt = $pdo->prepare("SELECT * FROM instructions WHERE meal_id = ? ORDER BY step_number");
    $instructionsStmt->execute([$meal_id]);
    $instructions = $instructionsStmt->fetchAll(PDO::FETCH_ASSOC);

    $ingredientsStmt = $pdo->prepare("SELECT * FROM ingredients WHERE meal_id = ?");
    $ingredientsStmt->execute([$meal_id]);
    $ingredients = $ingredientsStmt->fetchAll(PDO::FETCH_ASSOC);
    
} else {
    header("Location: adminposts.php");
    exit();
}

if (isset($_POST['update'])) {
    // Process the form data and update the database here

    // Assuming the update is successful, redirect to adminposts.php
    header("Location: adminposts.php");
    exit();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Meal</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 150px;
        }

        h1, h3 {
            color: #007bff;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: vertical;
        }

        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background-color: #0056b3;
        }

        a {
            color: #007bff;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }

        a:hover {
            text-decoration: underline;
        }
        h1{
            color: #18392B;
        }
        .mt-4{
            color: #18392B;
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
        .logo {
            display: flex;
            align-items: center;
        }
        .logo h1{
            color: #16b978;
            font-size: 25px;
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
    <h1 class="mt-4 mb-4">Edit Meal</h1>

    <form method="post" action="adminposts.php">
        <input type="hidden" name="meal_id" value="<?php echo $meal_id; ?>">

        <div class="form-group">
            <label for="meal_name">Meal Name:</label>
            <input type="text" class="form-control" name="meal_name" value="<?php echo $meal['meal_name']; ?>" required>
        </div>

        <div class="form-group">
            <label for="video_link">Video Link:</label>
            <input type="text" class="form-control" name="video_link" value="<?php echo $meal['video_link']; ?>" required>
        </div>

        <div class="form-group">
            <label for="image_links">Image Links (one link per line):</label>
            <textarea name="image_links" id="image_links" rows="5" class="form-control"><?php
                foreach ($existingImages as $existingImage) {
                    echo $existingImage['image_link'] . "\n";
                }
            ?></textarea>
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea class="form-control" name="description" rows="5"><?php echo $meal['description']; ?></textarea>
        </div>

        <h3 class="mt-4">Instructions</h3>
        <div class="form-group">
            <label for="all_steps">All Steps:</label>
            <textarea class="form-control" name="all_steps" rows="10"><?php
                foreach ($instructions as $instruction) {
                    echo $instruction['step_description'] . "\n";
                }
            ?></textarea>
        </div>

        <h3 class="mt-4">Ingredients</h3>
        <div class="form-group">
            <label for="all_ingredients">All Ingredients:</label>
            <textarea class="form-control" name="all_ingredients" rows="10"><?php
                foreach ($ingredients as $ingredient) {
                    echo $ingredient['ingredient_name'] . "\n";
                }
            ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary" name="update">Update Recipe</button>  
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
