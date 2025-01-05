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

    $ingredientsStmt = $pdo->prepare("SELECT ingredient_name, alt_ingredients FROM ingredients WHERE meal_id = ?");
    $ingredientsStmt->execute([$meal_id]);
    $ingredients = $ingredientsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all images associated with the meal_id from meal_images table
    $imagesStmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ?");
    $imagesStmt->execute([$meal_id]);
    $images = $imagesStmt->fetchAll(PDO::FETCH_ASSOC);

    $nutriInfoStmt = $pdo->prepare("SELECT * FROM nutritional_info WHERE meal_id = ?");
    $nutriInfoStmt->execute([$meal_id]);
    $nutriInfo = $nutriInfoStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: 9customer.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_recipe'])) {
    try {
        // Delete related entries in 'ratings' table first
        $deleteRatingsStmt = $pdo->prepare("DELETE FROM ratings WHERE meal_id = ?");
        $deleteRatingsStmt->execute([$meal_id]);

        // Delete related entries in 'favorites' table
        $deleteFavoritesStmt = $pdo->prepare("DELETE FROM favorites WHERE meal_id = ?");
        $deleteFavoritesStmt->execute([$meal_id]);

        // Delete related entries in 'instructions' table
        $deleteInstructionsStmt = $pdo->prepare("DELETE FROM instructions WHERE meal_id = ?");
        $deleteInstructionsStmt->execute([$meal_id]);

        // Delete related entries in 'ingredients' table
        $deleteIngredientsStmt = $pdo->prepare("DELETE FROM ingredients WHERE meal_id = ?");
        $deleteIngredientsStmt->execute([$meal_id]);

        // Delete related entries in 'meal_images' table
        $deleteImagesStmt = $pdo->prepare("DELETE FROM meal_images WHERE meal_id = ?");
        $deleteImagesStmt->execute([$meal_id]);

        // Now delete the meal itself
        $deleteMealStmt = $pdo->prepare("DELETE FROM meals WHERE meal_id = ?");
        $deleteMealStmt->execute([$meal_id]);

        header("Location: 12user_profile.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_recipe'])) {
    header("Location: 16editpost.php?meal_id=$meal_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>eSangkap</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
        }

        .logo-container {
            padding: 7px;
            position: fixed;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .logo-container img {
            height: 60px;
            width: auto;
            margin-right: 10px;
            border-radius: 50%;
        }

        .logo-title {
            color: #f04e23;
        }

        .sidebar {
            background-color: #f04e23;
            height: 100%;
            width: 250px;
            position: fixed;
            top: 70px;
            left: 0;
            overflow-x: hidden;
            padding-top: 20px;
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

        .sidebar a i {
            margin-right: 15px;
        }

        .sidebar a.active {
            background-color: #ffcccb;
            color: darkred;
        }

        .sidebar a:hover {
            background-color: white;
            color: darkred;
        }

        .container {
            margin: 0 auto;
            width: 50%;
            padding: 20px;
            max-width: 900px;
            background-color: #fff;
            margin-top: 100px;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }


        img {
            width: 100%;
            height: 400px;
            margin-bottom: 10px;
            border-radius: 10px;
        }

        h2 {
            color: #f04e23;
        }

        h3 {
            color: black;
        }

        p {
            font-size: 16px;
            color: #333;
            margin: 10px 0;
        }

        .buttons {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
        }

        .buttons a,
        .buttons button {
            background: none;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            /* Set the font to Poppins */
            font-size: 1rem;
            color: black;
            padding: 10px 20px;
            border-radius: 5px;
            text-align: center;
            display: flex;
            align-items: center;
            text-decoration: none;
            /* Remove underline */
        }


        .list-box ol.rounded-list {
            counter-reset: li;
        }

        .list-box ol.rounded-list li {
            position: relative;
            padding: 15px;
            background: #f3f3f3;
            border-radius: 5px;
            margin-top: 12px;
            list-style: none;
        }

        .list-box ol.rounded-list li:before {
            content: counter(li);
            counter-increment: li;
            position: absolute;
            left: -2em;
            top: 50%;
            transform: translateY(-50%);
            background: #f04e23;
            height: 30px;
            width: 30px;
            line-height: 30px;
            text-align: center;
            font-weight: bold;
            border-radius: 50%;
            color: white;
        }

        .watch-video {
            display: inline-block;
            padding: 10px 16px;
            background-color: #f04e23;
            color: #fff;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .watch-video:hover {
            background-color: darkred;
            color: white;
        }

        .watch-video i {
            margin-right: 8px;
        }

        .meal-header {
            display: flex;
            justify-content: space-between;
        }

        .meal-header h1 {
            margin: 0;
            font-size: 24px;
            color: #f04e23;
        }

        .views {
            font-size: 14px;
            background-color: #f04e23;
            color: white;
            border-radius: 20px;
            max-width: fit-content;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 720px;
        }

        #toggle-alt-ingredients {
            font-family: 'Poppins', sans-serif;
            border: none;
            display: inline-block;
            padding: 10px 16px;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            margin-top: 20px;
            text-align: left;
            width: auto;
            background-color: white;
        }

        #toggle-alt-ingredients.green-text {
            color: green;
        }
    </style>
</head>

<body>
    <div class="logo-container">
        <img src="logo.jpg" alt="Logo">
        <h2 class="logo-title">eSangkap</h2>
    </div>
    <div class="sidebar">
        <a href="9customer.php"><i class="fa fa-fw fa-home"></i> Home</a>
        <a href="favoritescreen.php"><i class="fas fa-heart"></i> Favorites</a>
        <a href="view_categories.php"><i class="fas fa-list"></i> Categories</a>
        <a href="12user_profile.php" class="active"><i class="fas fa-user"></i> Profile</a>
        <a href="4logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <div class="container">
        <div class="buttons">
            <a href="shoppingList.php?meal_id=<?php echo $meal_id; ?>"><i class="fas fa-shopping-cart"></i></a>
            <a href="16editpost.php?meal_id=<?php echo $meal_id; ?>"><i class="fas fa-edit"></i></a>
            <form method="POST" onsubmit="return confirmDelete()">
                <button type="submit" name="delete_recipe"><i class="fas fa-trash-alt"></i></button>
            </form>

            <script>
                function confirmDelete() {
                    return confirm('Are you sure you want to delete this recipe?');
                }
            </script>
        </div>
        <img src="<?php echo $images[0]['image_link']; ?>" alt="Meal Image">
        <div class="meal-header">
            <div>
                <h1><?php echo $meal['meal_name']; ?></h1>

            </div>
            <div>
                <a class="watch-video" href="shoppingList.php?meal_id=<?php echo $meal_id; ?>" target="_blank">
                    <i class="fas fa-shopping-cart"></i> Where to Buy Ingredients
                </a>
                <a class="watch-video" href="<?php echo $meal['video_link']; ?>" target="_blank">
                    <i class="fas fa-play-circle"></i> Watch Video
                </a>
            </div>

        </div>
        <h3>Description:</h3>
        <p><?php echo $meal['description']; ?></p>
        <p class="views">
            <i class="fas fa-eye"></i> Views: <?php echo $meal['views']; ?>
        </p>
        <button class="button" id="toggle-alt-ingredients">Show Alternative Ingredients &#9660;</button>

        <div class="list-box">
            <ol class="rounded-list">
                <?php foreach ($ingredients as $ingredient) { ?>
                    <li>
                        <?php echo $ingredient['ingredient_name']; ?>
                        <?php if (!empty($ingredient['alt_ingredients'])) { ?>
                            <br><span class="alt-ingredient" style="font-size: 0.9rem; color: #888; display: none;">Alternative: <?php echo $ingredient['alt_ingredients']; ?></span>
                        <?php } ?>
                    </li>
                <?php } ?>
            </ol>
        </div>

        <script>
            document.getElementById("toggle-alt-ingredients").addEventListener("click", function() {
                // Get all alternative ingredients elements
                const altIngredients = document.querySelectorAll(".alt-ingredient");

                // Toggle visibility for each alternative ingredient
                altIngredients.forEach(ingredient => {
                    if (ingredient.style.display === "none" || ingredient.style.display === "") {
                        ingredient.style.display = "inline";
                    } else {
                        ingredient.style.display = "none";
                    }
                });

                // Change button text based on the current state
                const button = document.getElementById("toggle-alt-ingredients");
                if (button.textContent.includes("Show")) {
                    button.innerHTML = "Hide Alternative Ingredients &#9650;"; // Up arrow when hidden
                } else {
                    button.innerHTML = "Show Alternative Ingredients &#9660;"; // Down arrow when shown
                }
            });
        </script>



        <!-- Instructions -->
        <h3>Instructions</h3>
        <div class="list-box">
            <ol class="rounded-list">
                <?php foreach ($instructions as $instruction) { ?>
                    <li><?php echo $instruction['step_description']; ?></li>
                <?php } ?>
            </ol>
        </div>

        <!-- Nutritional Info -->
        <h3>Nutritional Info</h3>
        <div class="list-box">
            <ol class="rounded-list">
                <?php foreach ($nutriInfo as $info) { ?>
                    <li><?php echo $info['nutrition_text']; ?></li>
                <?php } ?>
            </ol>
        </div>
    </div>
</body>

</html>