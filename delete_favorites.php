<?php
session_start();
require('0conn.php'); 

if (!isset($_SESSION['username'])) {
    echo "Please log in to delete meals from your favorites.";
    exit;
}

$username = $_SESSION['username']; 

if (isset($_GET['meal_id'])) {
    $meal_id = $_GET['meal_id'];

    $sql = "DELETE FROM favorites WHERE username = ? AND meal_id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt === false) {
        die("Error preparing query: " . mysqli_error($conn));
    }


    mysqli_stmt_bind_param($stmt, 'si', $username, $meal_id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: favoritescreen.php?success=Meal deleted successfully");
    } else {
        echo "Error deleting meal from favorites: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
} else {
    echo "No meal ID specified.";
}
mysqli_close($conn);
