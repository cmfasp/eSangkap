<?php 
session_start();
require("0conn.php");

// Database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Form validation function
function validateInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Initialize variables and errors
$errors = [];
$success_message = "";
$is_preview = false;

// Check if user is logged in
if (!isset($_SESSION["username"])) {
    die("Error: You must be logged in to submit a recipe.");
}

// Check if user exists in the database
$username = $_SESSION["username"];
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    die("Error: User does not exist.");
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    $recipe_name = validateInput($_POST["recipe_name"] ?? '');
    $category_id = validateInput($_POST["category_id"] ?? '');
    $video_link = filter_var($_POST["video_link"] ?? '', FILTER_SANITIZE_URL);
    $instructions = validateInput($_POST["instructions"] ?? '');
    $ingredients = validateInput($_POST["ingredients"] ?? '');
    $image_links = validateInput($_POST["image_links"] ?? '');
    $short_description = validateInput($_POST["short_description"] ?? '');
    $whereBuy = validateInput($_POST["whereBuy"] ?? '');
    $nutriInfo = validateInput($_POST["nutriInfo"] ?? '');
    $alt_ingredients = validateInput($_POST["alt_ingredients"] ?? '');

    if (empty($recipe_name)) $errors['recipe_name'] = "<span style='color:red;'>Recipe name is required.</span>";
    if (empty($category_id)) $errors['category_id'] = "<span style='color:red;'>Category is required.</span>";
    if (empty($video_link) || !filter_var($video_link, FILTER_VALIDATE_URL)) $errors['video_link'] = "<span style='color:red;'>Valid video link is required.</span>";
    if (empty($instructions)) $errors['instructions'] = "<span style='color:red;'>Instructions are required.</span>";
    if (empty($ingredients)) $errors['ingredients'] = "<span style='color:red;'>Ingredients are required.</span>";
    if (empty($image_links)) $errors['image_links'] = "<span style='color:red;'>Image links are required.</span>";
    if (empty($short_description)) $errors['short_description'] = "<span style='color:red;'>Short description is required.</span>";
    if (empty($whereBuy)) $errors['whereBuy'] = "<span style='color:red;'>Where to buy information is required.</span>";
    if (empty($nutriInfo)) $errors['nutriInfo'] = "<span style='color:red;'>Nutritional information is required.</span>";

    // Preview feature
    if (isset($_POST['preview'])) {
        $is_preview = true;
    }

    // Proceed only if no errors and not previewing
    if (empty($errors) && !$is_preview) {
        // Insert recipe into database
        $stmt = $pdo->prepare("INSERT INTO meals (meal_name, category_id, video_link, date_created, username, description, where_buy) VALUES (?, ?, ?, NOW(), ?, ?, ?)");
        $stmt->execute([$recipe_name, $category_id, $video_link, $username, $short_description, $whereBuy]);

        $meal_id = $pdo->lastInsertId();

        // Insert image links
        $image_links_array = explode("\n", $image_links);
        foreach ($image_links_array as $image_link) {
            if (!empty(trim($image_link))) {
                $stmt = $pdo->prepare("INSERT INTO meal_images (meal_id, image_link) VALUES (?, ?)");
                $stmt->execute([$meal_id, trim($image_link)]);
            }
        }

        // Insert instructions
        $instructions_array = explode("\n", $instructions);
        foreach ($instructions_array as $step_number => $step_description) {
            $stmt = $pdo->prepare("INSERT INTO instructions (meal_id, step_number, step_description) VALUES (?, ?, ?)");
            $stmt->execute([$meal_id, $step_number + 1, trim($step_description)]);
        }

        // Insert ingredients
        $ingredients_array = explode("\n", $ingredients);
        $alt_ingredients_array = explode("\n", $alt_ingredients);
        for ($i = 0; $i < max(count($ingredients_array), count($alt_ingredients_array)); $i++) {
            $ingredient_name = $ingredients_array[$i] ?? null;
            $alt_ingredient_name = $alt_ingredients_array[$i] ?? null;
            $stmt = $pdo->prepare("INSERT INTO ingredients (meal_id, ingredient_name, alt_ingredients) VALUES (?, ?, ?)");
            $stmt->execute([$meal_id, trim($ingredient_name), trim($alt_ingredient_name)]);
        }

        // Insert nutritional info
        $nutriInfo_array = explode("\n", $nutriInfo);
        foreach ($nutriInfo_array as $info) {
            $stmt = $pdo->prepare("INSERT INTO nutritional_info (meal_id, nutrition_text) VALUES (?, ?)");
            $stmt->execute([$meal_id, trim($info)]);
        }

        $success_message = "<script>alert('Recipe successfully added!');</script>";
    }
}
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
        }

        .logo-container {
            padding: 19px;
            position: fixed;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: flex;
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

        .logo {
            width: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        title {
            color: #f04e23;
        }

        h2 {
            color: black;
            margin-left: 2px;
            display: inline-block;
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
            margin: 20px auto;
            height: auto;
            width: auto;
            padding: 20px;
            background: #fff;
        }


        h2 {
            text-align: center;
            color: black;
            margin-bottom: 20px;
            margin-top: 100px;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .form-title {
            text-align: center;
            color: darkred;
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 15px;
        }

        .form-group {
            width: 100%;
            margin-bottom: 10px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }


        input,
        select,
        textarea {
            width: 500px;
            padding: 15px;
            margin: 5px;
            background-color: rgb(244, 242, 242);
            /* Light grey background */
            border: none;
            /* Remove border */
            border-radius: 30px;
            box-sizing: border-box;
            outline: none;
            /* Remove focus outline */
            font-family: Arial, Helvetica, sans-serif;
        }

        .form-buttons {
            display: flex;
            /* Arrange buttons in a row */
            gap: 20px;
            /* Add space between buttons */
            width: 100%;
            /* Make the container span the full width */
            justify-content: space-between;
            /* Distribute buttons evenly across the width */
        }

        button {
            flex: 1;
            /* Make each button take equal width */
            padding: 15px;
            /* Add padding for height */
            background-color: darkred;
            color: white;
            border: none;
            border-radius: 25px;
            /* Add rounded corners */
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            /* Set font size */
            cursor: pointer;
            text-align: center;
            /* Center align the text */
        }

        button:hover {
            background-color: #e74c3c;
            /* Lighten button color on hover */
            box-shadow: 2px 4px 8px rgba(0, 0, 0, 0.2);
            /* Enhance shadow on hover */
        }

        #preview-section {
            margin-top: 20px;
            text-align: left;
            display: none;
        }

        #recipe-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-top: 10px;
            display: none;
            margin: 0 auto;
        }

        #popup {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #16b978;
            color: #fff;
            padding: 10px 20px;
            border-radius: 4px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .preview-image {
            width: 100%;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .brand-name {
            font-size: 1.8rem;
            font-weight: bold;
            color: #f04e23;
            font-family: 'Arial', sans-serif;
            margin: 0;
        }

        .readonly-input {
            font-weight: bold;
            color: #555;
            text-align: left;
        }

        textarea[readonly] {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            color: #555;
            resize: none;
        }
    </style>
    <script>
       
        function showPopupMessage(message) {
            var popup = document.getElementById("popup");
            var popupMessage = document.getElementById("popup-message");
            popupMessage.innerText = message;
            popup.style.display = "block";
            setTimeout(function() {
                popup.style.display = "none";
            }, 5000);
        }
    </script>


</head>

<body>

    <div class="logo-container">
        <img src="logo.jpg" alt="Logo" class="logo">
        <span class="brand-name">eSangkap</span>
    </div>


    <div class="sidebar">
        <a href="9customer.php"><i class="fa fa-fw fa-home"></i>Home</a>
        <a href="favoritesreen.php"><i class="fa-solid fas fa-heart"></i>Favorites</a>
        <a href="view_categories.php"><i class="fa-solid fa-list"></i>Categories</a>
        <a href="12user_profile.php" class="active"><i class="fas fa-user"></i>Profile</a>
        <a href="testimony.php"><i class="fas fa-user-friends"></i> Forum</a>
        <a href="4logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </div>
    <div class="container">
        <h2 class="form-title">Add New Recipe</h2>
        <div id="form-section">
            <form method="post" onsubmit="showPopupMessage('Meal added successfully');">
                <div class="form-row">
                    <div class="form-group">
                        <label for="recipe_name">Meal Name:</label>
                        <input type="text" name="recipe_name" id="recipe_name" placeholder="Write your meal name here" value="<?php echo htmlspecialchars($recipe_name ?? ''); ?>">
                        <?php if (!empty($errors['recipe_name'])) {
                            echo "<p class='error'>{$errors['recipe_name']}</p>";
                        } ?>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category:</label>
                        <select name="category_id" id="category_id">
                            <?php
                            $categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($categories as $category) {
                                echo "<option value='{$category['category_id']}'" . ($category['category_id'] == $category_id ? ' selected' : '') . ">{$category['category_name']}</option>";
                            }
                            ?>
                        </select>
                        <?php if (!empty($errors['category_id'])) {
                            echo "<p class='error'>{$errors['category_id']}</p>";
                        } ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="video_link">Video Link:</label>
                        <input type="text" name="video_link" id="video_link" placeholder="Add a youtube tutorial" value="<?php echo htmlspecialchars($video_link ?? ''); ?>">
                        <?php if (!empty($errors['video_link'])) {
                            echo "<p class='error'>{$errors['video_link']}</p>";
                        } ?>
                    </div>
                    <div class="form-group">
                        <label for="image_links">Image Links:</label>
                        <textarea name="image_links" id="image_links" rows="3" placeholder="Add image links here"><?php echo htmlspecialchars($image_links ?? ''); ?></textarea>
                        <?php if (!empty($errors['image_links'])) {
                            echo "<p class='error'>{$errors['image_links']}</p>";
                        } ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="short_description">Short Description:</label>
                        <textarea name="short_description" id="short_description" rows="3" placeholder="Add a short description of your meal"><?php echo htmlspecialchars($short_description ?? ''); ?></textarea>
                        <?php if (!empty($errors['short_description'])) {
                            echo "<p class='error'>{$errors['short_description']}</p>";
                        } ?>
                    </div>
                    <div class="form-group">
                        <label for="whereBuy">Where to Buy:</label>
                        <input type="text" name="whereBuy" id="whereBuy" placeholder="Write where to buy" value="<?php echo htmlspecialchars($whereBuy ?? ''); ?>">
                        <?php if (!empty($errors['whereBuy'])) {
                            echo "<p class='error'>{$errors['whereBuy']}</p>";
                        } ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ingredients">Ingredients:</label>
                        <textarea name="ingredients" id="ingredients" rows="5" placeholder="Add the list of ingredients here"><?php echo htmlspecialchars($ingredients ?? ''); ?></textarea>
                        <?php if (!empty($errors['ingredients'])) {
                            echo "<p class='error'>{$errors['ingredients']}</p>";
                        } ?>
                    </div>
                    <div class="form-group">
                        <label for="alt_ingredients">Alternative Ingredients:</label>
                        <textarea name="alt_ingredients" id="alt_ingredients" rows="5" placeholder="Add alternative ingredients here"><?php echo htmlspecialchars($alt_ingredients ?? ''); ?></textarea>
                        <?php if (!empty($errors['alt_ingredients'])) {
                            echo "<p class='error'>{$errors['alt_ingredients']}</p>";
                        } ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nutriInfo">Nutritional Information:</label>
                        <textarea name="nutriInfo" id="nutriInfo" rows="5" placeholder="Add nutritional information here"><?php echo htmlspecialchars($nutriInfo ?? ''); ?></textarea>
                        <?php if (!empty($errors['nutriInfo'])) {
                            echo "<p class='error'>{$errors['nutriInfo']}</p>";
                        } ?>
                    </div>
                    <div class="form-group">
                        <label for="instructions">Instructions:</label>
                        <textarea name="instructions" id="instructions" rows="5" placeholder="Add preparation instructions here"><?php echo htmlspecialchars($instructions ?? ''); ?></textarea>
                        <?php if (!empty($errors['instructions'])) {
                            echo "<p class='error'>{$errors['instructions']}</p>";
                        } ?>
                    </div>
                </div>

                <div class="form-buttons">
                    <button id="add-button" type="submit">Add Recipe</button>
                </div>
            </form>
        </div>

    </div>
</body>