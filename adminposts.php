
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

// Initialize a variable to store the success message
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_recipe'])) {
    $deleteStmt = $pdo->prepare("DELETE FROM meals WHERE meal_id = ?");
    $deleteStmt->execute([$meal_id]);

    // Set the success message
    $successMessage = "Meal deleted successfully.";

    // Redirect to adminprofile.php
    header("Location: adminprofile.php");
    exit();
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
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
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
    max-width: 100%; /* Ensure images don't exceed their original width */
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

        h3 {
            margin-top: 170px;
            color: #18392B;
            font-weight: bold;
            margin-left: 350px;
        }
        h4{
            font-weight: bold;
            color:  #4caf50;
        }
        
        .btn-secondary {
            background-color: #4caf50;
            color: #fff;
        }
        h1 {
            font-size: 25px;
            justify-content: center;
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

<h3>Meal Details</h3>
<div class="container">
    <h4> <?php echo $meal['meal_name']; ?></h4>
    <p>Video Link: <a href="<?php echo $meal['video_link']; ?>" target="_blank">Watch Video</a></p>

    <div class="row">
        <?php
        $meal_id = $meal['meal_id'];
        $images = getImages($pdo, $meal_id);
        foreach ($images as $image) {
            echo '<div class="col-md-4 mb-4">';
            echo "<img src='{$image['image_link']}' alt='Recipe Image' class='img-fluid'>";
            echo '</div>';
        }
        ?>
    </div>

    <h4>Instructions</h4>
    <ol>
        <?php foreach ($instructions as $instruction) { ?>
            <li><?php echo $instruction['step_description']; ?></li>
        <?php } ?>
    </ol>

    <h4>Ingredients</h4>
    <ul>
        <?php foreach ($ingredients as $ingredient) { ?>
            <li><?php echo $ingredient['ingredient_name']; ?></li>
        <?php } ?>
    </ul>

    <form method="post" action="" class="recipe-form">
    <button type="submit" name="edit_recipe" class="btn btn-primary">Edit</button>
    <button type="submit" name="delete_recipe" class="btn btn-danger">Delete</button>
</form>

    
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

