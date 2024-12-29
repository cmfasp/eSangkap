<?php
session_start();
require('0conn.php'); 


if (!isset($_SESSION['username'])) {
    echo "Please log in to add meals to your favorites.";
    exit;
}

$username = $_SESSION['username'];  


if (isset($_GET['meal_id'])) {
    $meal_id = $_GET['meal_id'];

  // checkingg if meal is exists
    $checkStmt = $conn->prepare("SELECT * FROM favorites WHERE username = ? AND meal_id = ?");
    $checkStmt->bind_param("si", $username, $meal_id);
    if (!$checkStmt->execute()) {
        die("Query Error: " . $checkStmt->error);
    }
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo "<script>alert('This meal is already in your favorites.');</script>";
    } else {
        
        $stmt = $conn->prepare("INSERT INTO favorites (username, meal_id, date_created) VALUES (?, ?, NOW())");
        $stmt->bind_param("si", $username, $meal_id);

        if ($stmt->execute()) {
            echo "<script>alert('Meal successfully added to favorites!');</script>";
        } else {
            die("Error adding meal: " . $stmt->error);
        }
    }
    header("Location: favoritescreen.php");
    exit;
} else {
    echo "No meal ID provided.";
}
