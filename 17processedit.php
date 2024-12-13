<?php
session_start();
require("0conn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_recipe'])) {
    $meal_id = $_POST['meal_id'];

    $meal_name = $_POST['meal_name'];
    $video_link = $_POST['video_link'];
    $image_links = array_map('trim', explode("\n", $_POST['image_links']));
    $description = $_POST['description'];
    $all_steps = $_POST['all_steps'];
    $all_ingredients = $_POST['all_ingredients'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $updateMealStmt = $pdo->prepare("UPDATE meals SET meal_name = ?, video_link = ?, description = ? WHERE meal_id = ?");
        $updateMealStmt->execute([$meal_name, $video_link, $description, $meal_id]);

        $deleteInstructionsStmt = $pdo->prepare("DELETE FROM instructions WHERE meal_id = ?");
        $deleteInstructionsStmt->execute([$meal_id]);
    
        // Insert updated instructions
        $insertInstructionsStmt = $pdo->prepare("INSERT INTO instructions (meal_id, step_number, step_description) VALUES (?, ?, ?)");
        $instructionsArray = explode("\n", $all_steps);
        foreach ($instructionsArray as $stepNumber => $step) {
            $insertInstructionsStmt->execute([$meal_id, $stepNumber + 1, trim($step)]);
        }
    
        // Delete existing images for the meal
        $deleteImagesStmt = $pdo->prepare("DELETE FROM meal_images WHERE meal_id = ?");
        $deleteImagesStmt->execute([$meal_id]);
    
        // Insert updated image links
        $insertImagesStmt = $pdo->prepare("INSERT INTO meal_images (meal_id, image_link) VALUES (?, ?)");
        foreach ($image_links as $image_link) {
            $insertImagesStmt->execute([$meal_id, trim($image_link)]);
        }

        // Delete existing ingredients for the meal
        $deleteIngredientsStmt = $pdo->prepare("DELETE FROM ingredients WHERE meal_id = ?");
        $deleteIngredientsStmt->execute([$meal_id]);
    
        // Insert updated ingredients
        $insertIngredientsStmt = $pdo->prepare("INSERT INTO ingredients (meal_id, ingredient_name) VALUES (?, ?)");
        $ingredientsArray = explode("\n", $all_ingredients);
        foreach ($ingredientsArray as $ingredientId => $ingredient) {
            $insertIngredientsStmt->execute([$meal_id, trim($ingredient)]);
        }
        header("Location: 16editpost.php?meal_id=$meal_id");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: 9customer.php");
    exit();
}
?>
