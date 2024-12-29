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
} else {
    header("Location: 5admin.php");
    exit();
}

$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_recipe'])) {
    try {
        // Delete related entries first to maintain referential integrity
        $pdo->prepare("DELETE FROM ratings WHERE meal_id = ?")->execute([$meal_id]);
        $pdo->prepare("DELETE FROM instructions WHERE meal_id = ?")->execute([$meal_id]);
        $pdo->prepare("DELETE FROM ingredients WHERE meal_id = ?")->execute([$meal_id]);
        $pdo->prepare("DELETE FROM meal_images WHERE meal_id = ?")->execute([$meal_id]);

        // Now delete the meal itself
        $deleteStmt = $pdo->prepare("DELETE FROM meals WHERE meal_id = ?");
        $deleteStmt->execute([$meal_id]);

        $successMessage = "Meal deleted successfully.";
        header("Location: adminprofile.php");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_recipe'])) {
    header("Location: admineditpost.php?meal_id=$meal_id");
    exit();
}

function getImages($pdo, $meal_id) {
    $stmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ?");
    $stmt->execute([$meal_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Details</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #ededed;
            margin: 0 auto;
            font-family: 'Lucida Sans', sans-serif;
            color: #18392B;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h1, h2, h3 {
            color: #4caf50;
        }
        img {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        ol, ul {
            margin-bottom: 15px;
        }
        a {
            color: #4caf50;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
            color: #18392B;
        }
        button {
            background-color: #4caf50;
            color: #fff;
            border: none;
            padding: 10px;
            cursor: pointer;
            margin-right: 10px;
            border-radius: 5px;
        }
        button:hover {
            background-color: #45a049;
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
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            padding-top: 90px;
            z-index: 1;
        }
        .topnav a {
            color: #f2f2f2;
            padding: 15px 25px;
            font-size: 17px;
        }
        .topnav a:hover {
            background-color: #04AA6D;
            color: white;
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
    <a href="adminViewPost.php">Home</a>
    <a href="adminprofile.php">Admin Profile</a>
    <a href="5admin.php">Manage Recipe</a>
    <a href="4logout.php">Logout</a>
</div>

<h3>Meal Details</h3>
<div class="container">
    <h4><?php echo $meal['meal_name']; ?></h4>
    <p>Video Link: <a href="<?php echo $meal['video_link']; ?>" target="_blank">Watch Video</a></p>
    <div class="row">
        <?php $images = getImages($pdo, $meal['meal_id']);
        foreach ($images as $image) {
            echo '<div class="col-md-4 mb-4">';
            echo "<img src='{$image['image_link']}' alt='Recipe Image'>";
            echo '</div>';
        } ?>
    </div>
    <h4>Instructions</h4>
    <ol><?php foreach ($instructions as $instruction) {
        echo "<li>{$instruction['step_description']}</li>"; } ?></ol>
    <h4>Ingredients</h4>
    <ul><?php foreach ($ingredients as $ingredient) {
        echo "<li>{$ingredient['ingredient_name']}</li>"; } ?></ul>
    <form method="post">
        <button type="submit" name="edit_recipe">Edit</button>
        <button type="submit" name="delete_recipe">Delete</button>
    </form>
</div>
</body>
</html>
