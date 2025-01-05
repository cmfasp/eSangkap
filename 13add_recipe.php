<?php
session_start();

require("0conn.php");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

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
        isset($_POST["whereBuy"]) &&
        isset($_POST["nutriInfo"]) &&
        isset($_POST["alt_ingredients"])
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
            $whereBuy = $_POST["whereBuy"];

            // Fetch category name based on category ID
            $categoryStmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
            $categoryStmt->execute([$category_id]);
            $category = $categoryStmt->fetch();

            $stmt = $pdo->prepare("INSERT INTO meals (meal_name, category_id, video_link, date_created, username, description, where_buy) VALUES (?, ?, ?, NOW(), ?, ?, ?)");
            $stmt->execute([$recipe_name, $category_id, $video_link, $username, $short_description, $whereBuy]);

            $meal_id = $pdo->lastInsertId();

            // Handle multiple images
            $image_links = explode("\n", $image_links);
            foreach ($image_links as $image_link) {
                if (!empty(trim($image_link))) {
                    $stmt = $pdo->prepare("INSERT INTO meal_images (meal_id, image_link) VALUES (?, ?)");
                    $stmt->execute([$meal_id, trim($image_link)]);
                }
            }

            $instructions = explode("\n", $_POST["instructions"]);
            foreach ($instructions as $step_number => $step_description) {
                $stmt = $pdo->prepare("INSERT INTO instructions (meal_id, step_number, step_description) VALUES (?, ?, ?)");
                $stmt->execute([$meal_id, $step_number + 1, trim($step_description)]);
            }

            $ingredients = explode("\n", $_POST["ingredients"]);
            $alt_ingredients = explode("\n", $_POST["alt_ingredients"]);

            for ($i = 0; $i < max(count($ingredients), count($alt_ingredients)); $i++) {

                $ingredient_name = isset($ingredients[$i]) && !empty($ingredients[$i]) ? trim($ingredients[$i]) : null;


                $alt_ingredient_name = isset($alt_ingredients[$i]) && !empty($alt_ingredients[$i]) ? trim($alt_ingredients[$i]) : null;


                $stmt = $pdo->prepare("INSERT INTO ingredients (meal_id, ingredient_name, alt_ingredients) VALUES (?, ?, ?)");
                $stmt->execute([$meal_id, $ingredient_name, $alt_ingredient_name]);
            }

            $nutriInfo = explode("\n", $_POST["nutriInfo"]);
            foreach ($nutriInfo as $info) {
                $stmt = $pdo->prepare("INSERT INTO nutritional_info (meal_id, nutrition_text) VALUES (?, ?)");
                $stmt->execute([$meal_id, trim($info)]);
            }
        } else {
            echo "Error: User does not exist.";
        }
    }
}

function generateRecipePreview($pdo, $meal_id)
{
    $stmt = $pdo->prepare("SELECT * FROM meals WHERE meal_id = ?");
    $stmt->execute([$meal_id]);
    $recipe = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ?");
    $stmt->execute([$meal_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM instructions WHERE meal_id = ? ORDER BY step_number");
    $stmt->execute([$meal_id]);
    $instructions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM ingredients WHERE meal_id = ?");
    $stmt->execute([$meal_id]);
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $preview = "<h2>Recipe Preview</h2>";
    $preview .= "<h3>{$recipe['meal_name']}</h3>";
    $preview .= "<p>Video Link: {$recipe['video_link']}</p>";

    $preview .= "<p>Category: {$recipe['category_id']}</p>";
    $preview .= "<p>Short Description: {$recipe['description']}</p>";

    $preview .= "<h3>Instructions</h3>";
    $preview .= "<ol>";
    foreach ($instructions as $instruction) {
        $preview .= "<li>{$instruction['step_description']}</li>";
    }
    $preview .= "</ol>";

    $preview .= "<h3>Ingredients</h3>";
    $preview .= "<ul>";
    foreach ($ingredients as $ingredient) {
        $preview .= "<li>{$ingredient['ingredient_name']}</li>";
    }
    $preview .= "<h3>Images</h3>";
    $preview .= "<div class='image-gallery'>";
    foreach ($images as $image) {
        $preview .= "<img src='{$image['image_link']}' alt='Meal Image' class='preview-image'>";
    }
    $preview .= "</div>";
    $preview .= "</ul>";

    return $preview;
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
            /* Adjust font size for H2 */
            font-weight: bold;
            /* Emphasize the brand name */
            color: #f04e23;
            /* Matches the theme */
            font-family: 'Arial', sans-serif;
            /* Clean and modern font */
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
        function togglePreview() {
            var formSection = document.getElementById("form-section");
            var previewSection = document.getElementById("preview-section");
            var previewButton = document.getElementById("preview-button");
            var addButton = document.getElementById("add-button");
            var editButton = document.getElementById("edit-button");

            if (formSection.style.display === "block") {
                formSection.style.display = "none";
                previewSection.style.display = "block";
                previewButton.innerText = "Edit";
                addButton.style.display = "none";
                editButton.style.display = "inline";
                displayPreview();
            } else {
                formSection.style.display = "block";
                previewSection.style.display = "none";
                previewButton.innerText = "Preview";
                addButton.style.display = "inline";
                editButton.style.display = "none";
            }
        }

        function displayPreview() {
            var readonlyInputs = document.getElementsByClassName("readonly-input");
            var inputs = document.getElementsByTagName("input");
            var selects = document.getElementsByTagName("select");
            var textareas = document.getElementsByTagName("textarea");

            for (var i = 0; i < readonlyInputs.length; i++) {
                readonlyInputs[i].innerText = "";
                if (i < inputs.length) {
                    readonlyInputs[i].innerText = inputs[i].value;
                } else if (i < inputs.length + selects.length) {
                    var selectedIndex = selects[i - inputs.length].selectedIndex;
                    readonlyInputs[i].innerText = selects[i - inputs.length].options[selectedIndex].text;
                } else if (i < inputs.length + selects.length + textareas.length) {
                    readonlyInputs[i].innerText = textareas[i - inputs.length - selects.length].value;
                }
            }

            displayImageGallery();
        }

        function displayImageGallery() {
            const readonlyInputs = document.getElementsByClassName("readonly-input");
            const imageGallery = document.querySelector('.image-gallery');
            const imageLinksTextarea = document.getElementById("image_links");
            const imageLinks = imageLinksTextarea.value.trim().split('\n');

            imageGallery.innerHTML = ""; // Clear existing images

            if (imageLinks.length > 0) {
                imageLinks.forEach(imageLink => {
                    if (imageLink !== "") {
                        const imageElement = document.createElement('img');
                        imageElement.src = imageLink;
                        imageElement.alt = 'Meal Image';
                        imageElement.className = 'preview-image';
                        imageGallery.appendChild(imageElement);
                    }
                });
            }
        }

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
        <a href="4logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </div>
    <div class="container">
        <h2 class="form-title">Add New Recipe</h2>
        <div id="form-section">
            <form method="post" onsubmit="showPopupMessage('Meal added successfully');">
                <div class="form-row">
                    <div class="form-group">
                        <label for="recipe_name">Meal Name:</label>
                        <input type="text" name="recipe_name" id="recipe_name" placeholder="Write your meal name here" required>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category:</label>
                        <select name="category_id" id="category_id" required>
                            <?php
                            $categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($categories as $category) {
                                echo "<option value='{$category['category_id']}'>{$category['category_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="video_link">Video Link:</label>
                        <input type="text" name="video_link" id="video_link" placeholder="Add a youtube tutorial" required>
                    </div>
                    <div class="form-group">
                        <label for="image_links">Image Links:</label>
                        <input name="image_links" id="image_links" rows="3" placeholder="Add image links here"></input>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="short_description">Short Description:</label>
                        <textarea name="short_description" id="short_description" rows="3" placeholder="Add a short description of your meal" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="recipe_name">Where to buy:</label>
                        <input type="text" name="whereBuy" id="whereBuy" placeholder="Write your meal name here" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                    <label for="ingredients">Ingredients:</label>
                    <textarea name="ingredients" id="ingredients" rows="5" placeholder="Add the list of ingredients here" required></textarea>
              
                       
                    </div>
                    <div class="form-group">
                    <label for="ingredients">Alternative Ingredients:</label>
                    <textarea name="alt_ingredients" id="alt_ingredients" rows="5" placeholder="Add alternative ingredients here"></textarea>
                       
                    </div>

                </div>

<div class="form-row">
    <div class="form-group">
        <label for="nutriInfo">Nutritional Information:</label>
        <textarea name="nutriInfo" id="nutriInfo" rows="5" placeholder="Add nutritional information here" required></textarea>
    </div>
    <div class="form-group">
        <label for="instructions">Instructions:</label>
        <textarea name="instructions" id="instructions" rows="5" placeholder="Add preparation instructions here" required></textarea>
    </div>
</div>



                              
        <div class="form-buttons">
            <button id="preview-button" type="button" onclick="togglePreview()">Preview</button>
            <button id="add-button" type="submit">Add Recipe</button>
            <button id="edit-button" type="button" style="display: none;">Edit</button>
        </div>
        </form>
    </div>

    <div id="popup" style="display: none;">
        <p id="popup-message"></p>
    </div>
    <div id="preview-section" style="display: none;">
        <div id="readonly-section">
            <p>Meal Name: <span class="readonly-input meal-name"></span></p>
            <p>Video Link: <span class="readonly-input short-description"></span></p>
            <p>Image: <span class="readonly-input video-link"></span></p>
            <img id="recipe-image" src="" alt="Recipe Image" style="max-width: 100%; display: none;">
            <h3>Where to buy the ingredients</h3>
            <p class="readonly-input whereBuy"></p>
            <h3>Category</h3>
        <p class="readonly-input category"></p>
        <h3>Short Description</h3>
        <p class="readonly-input description"></p>
        <h3>Ingredients</h3>
        <p class="readonly-input ingredients"></p>
        <h3>Alternative Ingredients</h3>
        <p class="readonly-input alt_ingredients"></p>
        <h3>Nutritonal Information</h3>
        <p class="readonly-input nutriInfo"></p>
        <h3>Instruction</h3>
        <p class="readonly-input instructions"></p>
        </div>
        <div class="form-buttons">
            <button id="preview-button" type="button" onclick="togglePreview()">Preview</button>
            <button id="add-button" type="submit">Add</button>
            <button id="edit-button" type="button" style="display: none;" onclick="toggleEdit()">Edit</button>
        </div>
    </div>
</body>