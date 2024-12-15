<?php
session_start();
require('0conn.php'); // Include your database connection file

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo "Please log in to add meals to your favorites.";
    exit;
}

$username = $_SESSION['username'];  // Get username from session

// Check if meal_id is provided
if (isset($_GET['meal_id'])) {
    $meal_id = $_GET['meal_id'];

    // Prepare SQL query to insert the meal into favorites table
    $sql = "INSERT INTO favorites (username, meal_id, date_added) VALUES (?, ?, NOW())";

    // Prepare the statement
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt === false) {
        echo "Error preparing the query: " . mysqli_error($conn);
        exit;
    }

    // Bind parameters and execute the query
    mysqli_stmt_bind_param($stmt, 'si', $username, $meal_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "Meal added to favorites successfully!";
    } else {
        echo "Error adding meal to favorites: " . mysqli_error($conn);
    }

    // Close statement
    mysqli_stmt_close($stmt);
} else {
    echo "No meal ID specified.";
}

// Close the database connection
mysqli_close($conn);
?>
