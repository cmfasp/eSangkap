<?php
session_start();
require("0conn.php");
// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    // If not logged in, redirect to the login page with an error message
    $_SESSION['error_message'] = "You must log in first.";
    header("Location: 3login.php");
    exit();  // Stop further execution after the redirection
}

if (isset($_GET['meal_id'])) {
    $meal_id = $_GET['meal_id'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }

    // Fetch meal data
    $stmt = $pdo->prepare("SELECT * FROM meals WHERE meal_id = ?");
    $stmt->execute([$meal_id]);
    $meal = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch images
    $existingImagesStmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ?");
    $existingImagesStmt->execute([$meal_id]);
    $existingImages = $existingImagesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch instructions
    $instructionsStmt = $pdo->prepare("SELECT * FROM instructions WHERE meal_id = ? ORDER BY step_number");
    $instructionsStmt->execute([$meal_id]);
    $instructions = $instructionsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch alt_ingredients
    $ingredientsStmt = $pdo->prepare("SELECT * FROM ingredients WHERE meal_id = ?");
    $ingredientsStmt->execute([$meal_id]);
    $ingredients = $ingredientsStmt->fetchAll(PDO::FETCH_ASSOC);

    $altingredientsStmt = $pdo->prepare("SELECT alt_ingredients FROM ingredients WHERE meal_id = ?");
    $altingredientsStmt->execute([$meal_id]);
    $altingredients = $altingredientsStmt->fetchAll(PDO::FETCH_ASSOC);


    // Fetch categories
    $categoryStmt = $pdo->prepare("SELECT * FROM categories");
    $categoryStmt->execute();
    $categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

    $nutriInfoStmt = $pdo->prepare("SELECT * FROM nutritional_info WHERE meal_id = ?");
    $nutriInfoStmt->execute([$meal_id]);
    $nutriInfo = $nutriInfoStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: 9customer.php");
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
    <title>Edit Recipe - eSangkap</title>
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
        }

        .sidebar a i {
            margin-right: 15px;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px 25px;
            text-decoration: none;
            font-size: 15px;
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
            margin: 100px auto 20px;
            padding: 20px;
            max-width: 1300px;
            background-color: #fff;
            border-radius: 10px;
            align-items: center;
            margin-left: 550px;
        }

        h1 {
            color: darkred;
        }

        .form-container label {
            font-weight: bold;
            margin-top: 15px;
            display: block;
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            gap: 65px;
        }

        .form-row div {
            width: 32%;

        }

        .form-container input,
        .form-container textarea,
        .form-container select {
            width: 210%;
            max-width: 700px;
            padding: 15px;
            margin: 5px;
            background-color: rgb(244, 242, 242);
            border: none;
            border-radius: 30px;
            box-sizing: border-box;
            outline: none;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 15px;
        }

        .form-container button {
            background-color: darkred;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 20px;
            cursor: pointer;
            align-items: center;
            align-self: center;
            /* Center the button horizontally */
            width: 135%;
            /* Make the button as wide as the input and textarea fields */
            margin-top: 20px;
            /* Add some space above the button */
        }

        .form-container button:hover {
            background-color: #c0392b;
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
        <a href="favoritesreen.php"><i class="fas fa-heart"></i> Favorites</a>
        <a href="view_categories.php"><i class="fas fa-list"></i> Categories</a>
        <a href="12user_profile.php" class="active"><i class="fas fa-user"></i> Profile</a>
        <a href="testimony.php"><i class="fas fa-user-friends"></i> Forum</a>
        <a href="4logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="container">
        <h1>Edit Recipe</h1>

        <form method="post" action="17processedit.php" class="form-container">
            <input type="hidden" name="meal_id" value="<?php echo $meal_id; ?>">

            <label for="meal_name">Meal Name:</label>
            <input type="text" name="meal_name" value="<?php echo htmlspecialchars($meal['meal_name']); ?>" required>

            <label for="category">Category:</label>
            <select name="meal_category" required>
                <?php foreach ($categories as $category) { ?>
                    <option value="<?php echo htmlspecialchars($category['category_id']); ?>"
                        <?php echo ($category['category_id'] == $meal['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </option>
                <?php } ?>
            </select>


            <div class="form-row">
                <div>
                    <label for="video_link">Video Link:</label>
                    <input type="text" name="video_link" value="<?php echo htmlspecialchars($meal['video_link']); ?>" required>
                </div>
                <div>
                    <label for="image_links">Image Links:</label>
                    <input type="text" name="image_links" value="<?php foreach ($existingImages as $image) {
                                                                        echo htmlspecialchars($image['image_link']) . " ";
                                                                    } ?>" placeholder="Enter image links separated by spaces" required>
                </div>
            </div>

            <label for="description">Short Description:</label>
            <textarea name="description" rows="3"><?php echo htmlspecialchars($meal['description']); ?></textarea>

            <label for="meal_name">Where to Buy:</label>
            <input type="text" name="whereBuy" value="<?php echo htmlspecialchars($meal['where_buy']); ?>" required>

            <label for="all_ingredients">Ingredients:</label>
            <textarea name="all_ingredients" rows="5"><?php foreach ($ingredients as $ingredient) {
                                                            echo htmlspecialchars($ingredient['ingredient_name']) . "\n";
                                                        } ?></textarea>
            <label for="alt_ingredients">Alternative Ingredients:</label>
            <textarea name="alt_ingredients" rows="5"><?php
                                                        foreach ($altingredients as $alt_ingredient) {
                                                            echo htmlspecialchars($alt_ingredient['alt_ingredients']) . "\n";
                                                        }
                                                        ?></textarea>
            <label for="all_steps">Instructions:</label>
            <textarea name="all_steps" rows="5"><?php foreach ($instructions as $instruction) {
                                                    echo htmlspecialchars($instruction['step_description']) . "\n";
                                                } ?></textarea>

            <label for="all_steps">Nutritional Info:</label>
            <textarea name="all_nutriInfo" rows="5"><?php foreach ($nutriInfo as $info) {
                                                        echo htmlspecialchars($info['nutrition_text']) . "\n";
                                                    } ?></textarea>

            <button type="submit">Save Changes</button>
        </form>
    </div>

</body>

</html>