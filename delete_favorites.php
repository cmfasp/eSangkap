<?php
session_start();
require('0conn.php'); // Include your database connection file

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo "Please log in to delete meals from your favorites.";
    exit;
}

$username = $_SESSION['username']; // Get username from session

// Check if meal_id is provided
if (isset($_GET['meal_id'])) {
    $meal_id = $_GET['meal_id'];

    // Prepare the SQL query to delete the favorite meal
    $sql = "DELETE FROM favorites WHERE username = ? AND meal_id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt === false) {
        die("Error preparing query: " . mysqli_error($conn));
    }


    mysqli_stmt_bind_param($stmt, 'si', $username, $meal_id);

    if (mysqli_stmt_execute($stmt)) {
        // Redirect back to the favorites page with a success message
        header("Location: favoritescreen.php?success=Meal deleted successfully");
    } else {
        echo "Error deleting meal from favorites: " . mysqli_error($conn);
    }

    // Close the statement
    mysqli_stmt_close($stmt);
} else {
    echo "No meal ID specified.";
}

// Close the database connection
mysqli_close($conn);
