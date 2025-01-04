<?php
session_start();
require("0conn.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $meal_id = $_POST['meal_id'];
    $meal_name = $_POST['meal_name'];
    $category_id = $_POST['meal_category'];
    $video_link = $_POST['video_link'];
    $image_links = explode(" ", $_POST['image_links']);
    $description = $_POST['description'];
    $whereBuy = $_POST['whereBuy'];
    $all_ingredients = $_POST['all_ingredients'];
    $alt_ingredients = $_POST['alt_ingredients'];
    $all_steps = $_POST['all_steps'];
    $all_nutriInfo = $_POST['all_nutriInfo'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if category_id exists in categories table
        $categoryCheckStmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
        $categoryCheckStmt->execute([$category_id]);
        if ($categoryCheckStmt->rowCount() == 0) {
            die("Error: Invalid category_id");
        }

        // Update meal data
        $stmt = $pdo->prepare("UPDATE meals SET meal_name = ?, category_id = ?, video_link = ?, description = ?, where_buy = ? WHERE meal_id = ?");
        $stmt->execute([$meal_name, $category_id, $video_link, $description, $whereBuy, $meal_id]);

        // Update images
        $existingImagesStmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ?");
        $existingImagesStmt->execute([$meal_id]);
        $existingImages = $existingImagesStmt->fetchAll(PDO::FETCH_ASSOC);

        $imageStmt = $pdo->prepare("UPDATE meal_images SET image_link = ? WHERE meal_id = ? AND image_link = ?");
        foreach ($image_links as $index => $image_link) {
            $current_image_link = $existingImages[$index]['image_link'] ?? '';
            $imageStmt->execute([$image_link, $meal_id, $current_image_link]);
        }

        // Update ingredients
        $deleteIngredientsStmt = $pdo->prepare("DELETE FROM ingredients WHERE meal_id = ?");
        $deleteIngredientsStmt->execute([$meal_id]);
        $ingredientsStmt = $pdo->prepare("INSERT INTO ingredients (meal_id, ingredient_name, alt_ingredients) VALUES (?, ?, ?)");
        $ingredients = explode("\n", $all_ingredients);
        $alt_ingredients_list = explode("\n", $alt_ingredients);
        for ($i = 0; $i < count($ingredients); $i++) {
            $ingredientsStmt->execute([$meal_id, $ingredients[$i], $alt_ingredients_list[$i]]);
        }

        // Update instructions
        $deleteInstructionsStmt = $pdo->prepare("DELETE FROM instructions WHERE meal_id = ?");
        $deleteInstructionsStmt->execute([$meal_id]);
        $instructionsStmt = $pdo->prepare("INSERT INTO instructions (meal_id, step_number, step_description) VALUES (?, ?, ?)");
        $instructions = explode("\n", $all_steps);
        foreach ($instructions as $step_number => $step_description) {
            $instructionsStmt->execute([$meal_id, $step_number + 1, $step_description]);
        }

        // Update nutritional info
        $deleteNutriInfoStmt = $pdo->prepare("DELETE FROM nutritional_info WHERE meal_id = ?");
        $deleteNutriInfoStmt->execute([$meal_id]);
        $nutriInfoStmt = $pdo->prepare("INSERT INTO nutritional_info (meal_id, nutrition_text) VALUES (?, ?)");
        $nutriInfoList = explode("\n", $all_nutriInfo);
        foreach ($nutriInfoList as $info) {
            $nutriInfoStmt->execute([$meal_id, $info]);
        }

        // Redirect to meal display page
        header("Location: 11meal_details_comments.php?meal_id=" . $meal_id);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: 9customer.php");
    exit();
}
