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
        isset($_POST["short_description"])
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

            // Fetch category name based on category ID
            $categoryStmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
            $categoryStmt->execute([$category_id]);
            $category = $categoryStmt->fetch();

            $stmt = $pdo->prepare("INSERT INTO meals (meal_name, category_id, video_link, date_created, username, description) VALUES (?, ?, ?, NOW(), ?, ?)");
            $stmt->execute([$recipe_name, $category_id, $video_link, $username, $short_description]);

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
            foreach ($ingredients as $ingredient_name) {
                $stmt = $pdo->prepare("INSERT INTO ingredients (meal_id, ingredient_name) VALUES (?, ?)");
                $stmt->execute([$meal_id, trim($ingredient_name)]);
            }
        } else {
            echo "Error: User does not exist.";
        }
    }
}

function generateRecipePreview($pdo, $meal_id) {
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
            margin-top: 200px;
            flex-grow: 1;
            background-color: #fff;
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        h2 {
            text-align: center;
            color: black;
            margin-bottom: 20px;
            margin-top: 150px;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            margin: 10px 0 5px;
            color: #16b978;
            width: 300px; 
            text-align: right;
        }

        input,
        select,
        textarea {
            width: 500px; 
            padding: 10px;
            margin: 5px 0 15px;
            border: 2px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }


        #buttons {
            text-align: center;
            margin-top: 20px;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #16b978;
            color: #fff;
            border: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #128a61;
        }

        #preview-section {
            margin-top: 20px;
            text-align: center;
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

        /* wag tanggalin from here */
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
        /* to here */
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
            setTimeout(function () {
                popup.style.display = "none";
            }, 5000);
        }
    </script>
</head>
<body>
<div class="logo-container">
        <div class="logo">
            <img src="logo.png" alt="Tastebud Logo">
            <h1>Tastebud</h1>
        </div>
    </div>
     
    <div class="topnav">
        <a href="12user_profile.php"><i class="fas fa-fw fa-user"></i> Profile</a>
        <a href="view_categories.php"><i class="fa-solid fa-list"></i>Categories</a>
        <a href="9customer.php"><i class="fa-solid fa-utensils"></i>User Recipes</a>
        <a href="14chat.php"><i class="fa-solid fa-comment"></i>Chat</a>
        <a href="4logout.php"><i class="fas fa-fw fa-sign-out"></i> Logout</a>
    </div>
    <div class="container">
    <h2>Add New Recipe</h2>
    <div id="form-section">
        <form method="post" onsubmit="showPopupMessage('Meal added successfully');">
            <div>
                <label for="recipe_name">Recipe Name:</label>
                <input type="text" name="recipe_name" id="recipe_name" required>
            </div>
            <div>
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
            <div>
                <label for="short_description">Short Description:</label>
                <textarea name="short_description" id="short_description" rows="3" class="form-control" required></textarea>
                </div>
            <div>
                <label for="video_link">Video Link:</label>
                <input type="text" name="video_link" id="video_link" required>
            </div>
            <div>
                <label for="instructions">Instructions:</label>
                <textarea name="instructions" id="instructions" rows="5" required></textarea>
            </div>
            <div>
                <label for="ingredients">Ingredients:</label>
                <textarea name="ingredients" id="ingredients" rows="5" required></textarea>
            </div>
            <div>
                <label for="image_links">Image Links:</label>
                <textarea name="image_links" id="image_links" rows="5" class="form-control"></textarea>
            </div>
            <div id="buttons">
                <button id="preview-button" type="button" onclick="togglePreview()">Preview</button>
                <button id="add-button" type="submit">Add Recipe</button>
                <button id="edit-button" type="button" style="display: none;">Edit</button>
            </div>
        </form>
    </div>
    
    <div id="popup" style="display: none;">
        <p id="popup-message" style="background-color: #4CAF50; color: white; text-align: center; padding: 10px;"></p>
    </div>
    <div id="preview-section">
        <div id="readonly-section">
            <p>Recipe Name: <span class="readonly-input"></span></p>
            <p>Video Link: <span class="readonly-input"></span></p>
            <p>Category: <span class="readonly-input"></span></p>
            <p>Short Description: <span class="readonly-input short-description"></span></p>
            <img id="recipe-image" src="" alt="Recipe Image" style="max-width: 100%; display: none;">
        <h3>Instructions</h3>
            <ol class="readonly-input"></ol>
        <h3>Ingredients</h3>
            <ul class="readonly-input"></ul>
        </div>
        <h3>Images</h3>
        <div class='image-gallery'></div>
        <div id="buttons">
            <button id="preview-button" type="button" onclick="togglePreview()">Edit</button>
            <button id="add-button" type="submit">Add Recipe</button>
        </div>
    </div>
</body>
</html>