<?php
session_start();

require("0conn.php");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    echo "You must log in as an admin to access this page.";
    header("Refresh: 3; Location: 5admin.php");
    exit();
}

//START OF MODIFICATION

$recipe_preview = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION["username"];

    if (
        isset($_POST["recipe_name"]) &&
        isset($_POST["category_id"]) &&
        isset($_POST["video_link"]) &&
        isset($_POST["instructions"]) &&
        isset($_POST["ingredients"]) &&
        isset($_POST["image_links"]) &&
        isset($_POST["short_description"]) &&
        isset($_POST["alt_ingredients"]) &&  // Add this check
        isset($_POST["where_buy"]) &&       // Add this check
        isset($_POST["nutri_info"])         // Add this check
    ) {
        $userCheckStmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $userCheckStmt->execute([$username]);
        $userExists = $userCheckStmt->fetch();

        if ($userExists) {
            $recipe_name = $_POST["recipe_name"];
            $category_id = $_POST["category_id"];
            $video_link = $_POST["video_link"];
            $image_links = $_POST["image_links"];
            $short_description = $_POST["short_description"];
            $alt_ingredients = $_POST["alt_ingredients"];
            $where_buy = $_POST["where_buy"];
            $nutri_info = $_POST["nutri_info"];

            // Fetch category name based on category ID
            $categoryStmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
            $categoryStmt->execute([$category_id]);
            $category = $categoryStmt->fetch();

            // Insert meal details including new fields
            $stmt = $pdo->prepare("INSERT INTO meals (meal_name, category_id, video_link, date_created, username, description, where_buy) VALUES (?, ?, ?, NOW(), ?, ?, ?)");
            $stmt->execute([$recipe_name, $category_id, $video_link, $username, $short_description, $where_buy]);

            $meal_id = $pdo->lastInsertId();

            // Insert alternative ingredients into the ingredients table
            if (!empty($alt_ingredients)) {
                $stmt = $pdo->prepare("UPDATE ingredients SET alt_ingredients = ? WHERE meal_id = ?");
                $stmt->execute([$alt_ingredients, $meal_id]);
            }

            // Insert nutritional information into the nutritional_info table
            if (!empty($nutri_info)) {
                $stmt = $pdo->prepare("INSERT INTO nutritional_info (meal_id, nutrition_text) VALUES (?, ?)");
                $stmt->execute([$meal_id, $nutri_info]);
            }

            // Handle multiple images
            $image_links = explode("\n", $image_links);
            foreach ($image_links as $image_link) {
                if (!empty(trim($image_link))) {
                    $stmt = $pdo->prepare("INSERT INTO meal_images (meal_id, image_link) VALUES (?, ?)");
                    $stmt->execute([$meal_id, trim($image_link)]);
                }
            }

            // Insert instructions as before
            $instructions = explode("\n", $_POST["instructions"]);
            foreach ($instructions as $step_number => $step_description) {
                $stmt = $pdo->prepare("INSERT INTO instructions (meal_id, step_number, step_description) VALUES (?, ?, ?)");
                $stmt->execute([$meal_id, $step_number + 1, trim($step_description)]);
            }

            // Insert ingredients as before
            $ingredients = explode("\n", $_POST["ingredients"]);
            foreach ($ingredients as $ingredient_name) {
                $stmt = $pdo->prepare("INSERT INTO ingredients (meal_id, ingredient_name) VALUES (?, ?)");
                $stmt->execute([$meal_id, trim($ingredient_name)]);
            }
        } else {
            echo "Error: User does not exist.";
        }
    }
}


?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Add Recipe</title>
    <style>
        #form-section {
            display: block;
        }

        #preview-section {
            display: none;
        }

        #buttons {
            text-align: center;
            margin-top: 20px;
        }
    </style>

</head>

<body>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
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
            padding: 20px;
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
h3{
    margin-top: 120px;
    color: darkred;
}

    .form-control,
    select,
    textarea {
        border-radius: 15px;
        background-color: rgb(244, 242, 242);
        padding: 10px 15px;
        border: none;
        font-size: 16px;
        margin-bottom: 15px;
    }

    .form-group label {
        font-weight: bold;
        margin-bottom: 5px;
        display: block;
    }

    textarea {
        resize: none;
    }

    /* Ensure the buttons are dark red without hover effects */
    .btn-primary, .btn-success, .btn-warning {
        background-color: darkred ;
        border-radius: 25px;
        border: none;
        width: 20%;
        padding: 10px;
    }

 

    #preview-section {
        text-align: center; /* Center the content in the preview section */
    }

    #preview-section .readonly-input {
        display: inline-block;
        text-align: center; /* Center the text in the preview */
    }
</style>

    </style>
    <div class="logo-container">
        <img src="logo.jpg" alt="Logo" class="logo">
        <h2 class="title">eSangkap</h2>
    </div>

    <div class="sidebar">
        <a class="nav-link" href="adminViewPost.php">
            <i class="fa fa-fw fa-home"></i>Home
        </a>
        <a class="nav-link" href="5admin.php">
            <i class="fa-solid fa-utensils"></i>Manage Recipe
        </a>
        <a class="nav-link" href="4logout.php">
            <i class="fas fa-fw fa-sign-out"></i>Logout
        </a>
    </div>


    <div class="container">
        <div id="form-section">
            <h3>Add New Recipe</h3>
            <form method="post" onsubmit="showPopupMessage('Meal added successfully');">
                <div class="recipe-name">
                    <label for="recipe_name">Recipe Name:</label>
                    <input type="text" name="recipe_name" id="recipe_name" class="form-control" required>
                </div>

                <div class="container2">
                    <div class="form-group">
                        <label for="category_id">Category:</label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <?php
                            $categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($categories as $category) {
                                echo "<option value='{$category['category_id']}'>{$category['category_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <div>
                            <label for="short_description">Short Description (max 100 words):</label>
                            <textarea name="short_description" id="short_description" rows="3" class="form-control" required></textarea>
                        </div>
                        <div>
                            <label for="video_link">Video Link:</label>
                            <input type="text" name="video_link" id="video_link" class="form-control" required>
                        </div>
                        <div>
                            <label for="image_links">Image Links:</label>
                            <input type="text" name="image_links"id="image_links" rows="5" class="form-control"></textarea>

                        </div>
                        <div>
                            <label for="instructions">Instructions (one step):</label>
                            <textarea name="instructions" id="instructions" rows="3" class="form-control" required></textarea>
                        </div>
                        <div>
                            <label for="ingredients">Ingredients (one ingredient per line):</label>
                            <textarea name="ingredients" id="ingredients" rows="5" class="form-control" required></textarea>
                        </div>
                    
                        <!-- Add new fields in your form -->

                        <label for="alt_ingredients">Alternative Ingredients:</label>
                        <textarea name="alt_ingredients" id="alt_ingredients" class="form-control" rows="3"></textarea>

                        <label for="where_buy">Where to Buy:</label>
                        <textarea name="where_buy" id="where_buy" class="form-control" rows="3"></textarea>

                        <label for="nutri_info">Nutritional Information:</label>
                        <textarea name="nutri_info" id="nutri_info" class="form-control" rows="3"></textarea>

                        <div class="text-center" id="buttons">
                           
                            <button id="add-button" type="submit" class="btn btn-success">Add Recipe</button>
                        </div>
                    </div>
            </form>
        </div>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>