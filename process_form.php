<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process the selected category ID
    $selectedCategoryId = $_POST["category"];

    // You can perform further actions with the selected category ID
    // For example, redirect to a page showing details of the selected category
    header("Location: category_details.php?category_id={$selectedCategoryId}");
    exit();
}
?>
