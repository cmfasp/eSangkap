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
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
<script>
    $(document).ready(function() {
        $(".alert").alert();
    });
</script>
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

    .logo-container {
        text-align: left;
        padding-bottom: 20px;
        display: flex;
        align-items: center;
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

    .image-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }


    .recipe-name {
        margin: 20px auto;
        background-color: #fff;
        border-radius: 5px;
        padding: 20px;
        margin-top: 5px;
    }


    .form-section {
        background-color: #ffffff;
        border-radius: 15px;
        padding: 30px;
    }

    .form-section h3 {
        margin-top: 100px;
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

    .btn-dark-red {
        background-color: darkred;
        color: white ;
        border-radius: 15px;
        border: none;
        font-size: 16px;
        padding: 10px 20px;
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
        } else {
            formSection.style.display = "block";
            previewSection.style.display = "none";
            previewButton.innerText = "Preview";
            addButton.style.display = "inline";
            editButton.style.display = "none";
        }
    }
</script>
</head>

<body>
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
        </a>
    </div>


    <div class="container">
        <div class="form-section">
            <h3>Add New Recipe</h3>
            <form method="post">
                <div class="form-group">
                    <label for="recipe_name">Recipe Name:</label>
                    <input type="text" name="recipe_name" id="recipe_name" class="form-control" required>
                </div>
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
                    <label for="video_link">Video Link:</label>
                    <input type="text" name="video_link" id="video_link" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="image_links">Image Links:</label>
                    <input type="image_links" id="image_links" rows="5" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label for="short_description">Short Description:</label>
                    <textarea name="short_description" id="short_description" rows="3" class="form-control"   placeholder="Add a short description of the meal" required></textarea>
                </div>
                <div class="form-group">
                    <label for="instructions">Instructions:</label>
                    <textarea name="instructions" id="instructions" rows="5" class="form-control" placeholder="Add the list of instructions here" required></textarea>
                </div>
                <div class="form-group">
                    <label for="ingredients">Ingredients:</label>
                    <textarea name="ingredients" id="ingredients" rows="5" class="form-control"  placeholder="Add the list of ingredients here" required></textarea>
                </div>
                <div class="form-group">
                    <label for="alt_ingredients">Alternative Ingredients:</label>
                    <textarea name="alt_ingredients" id="alt_ingredients" rows="3" class="form-control" placeholder="Add the list of alternative ingredients here" ></textarea>
                </div>
                <div class="form-group">
                    <label for="whereBuy">Where to Buy:</label>
                    <input type="text" name="whereBuy" id="whereBuy" class="form-control" placeholder="Add locations or stores">
                </div>
                <div class="form-group">
                    <label for="nutriInfo">Nutritional Information:</label>
                    <textarea name="nutriInfo" id="nutriInfo" rows="5" class="form-control" placeholder="Add nutritional details here"></textarea>
                </div>
                <div class="text-center" id="buttons">
                    <button id="preview-button" type="button" class="btn btn-dark-red" onclick="togglePreview()">Preview</button>
                    <button id="add-button" type="submit" class="btn btn-dark-red">Add Recipe</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>