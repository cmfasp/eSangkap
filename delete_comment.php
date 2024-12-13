<?php
session_start();
require("0conn.php");

if (isset($_GET['comment_id']) && isset($_GET['meal_id'])) {
    $comment_id = $_GET['comment_id'];
    $meal_id = $_GET['meal_id'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }

    // Verify that the comment belongs to the logged-in user
    $verifyStmt = $pdo->prepare("SELECT * FROM comments WHERE comment_id = ? AND user_name = ?");
    $verifyStmt->execute([$comment_id, $_SESSION['username']]);
    $comment = $verifyStmt->fetch(PDO::FETCH_ASSOC);

    if ($comment) {
        // Delete the comment
        $deleteStmt = $pdo->prepare("DELETE FROM comments WHERE comment_id = ?");
        $deleteStmt->execute([$comment_id]);

        // Redirect back to the meal details page
        header("Location: 11meal_details_comments.php?meal_id=$meal_id");
        exit();
    } else {
        echo "You don't have permission to delete this comment.";
        exit();
    }
} else {
    header("Location: 9customer.php");
    exit();
}
?>
