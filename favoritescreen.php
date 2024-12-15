<?php
session_start();
require('0conn.php'); // Include database connection

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo "Please log in to view your favorite meals.";
    exit;
}

$username = $_SESSION['username'];  // Get username from session

// Query to retrieve favorite meals along with category name
$sql = "SELECT m.*, f.date_added, c.category_name
        FROM favorites f
        JOIN meals m ON f.meal_id = m.meal_id
        JOIN categories c ON m.category_id = c.category_id
        WHERE f.username = ?
        ORDER BY f.date_added DESC";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    die("Error preparing query: " . mysqli_error($conn));
}

// Bind parameters and execute
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);

// Fetch result
$result = mysqli_stmt_get_result($stmt);
$favorites = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Close connection
mysqli_stmt_close($stmt);
mysqli_close($conn);

// Helper Function: Time Elapsed
function getTimeElapsedString($datetime)
{
    $timestamp = strtotime($datetime);
    $time_diff = time() - $timestamp;

    $units = [
        'year' => 31536000,
        'month' => 2592000,
        'week' => 604800,
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1
    ];

    foreach ($units as $unit => $value) {
        $count = floor($time_diff / $value);
        if ($count > 0) {
            return "$count $unit" . ($count > 1 ? 's' : '') . " ago";
        }
    }
    return "Just now";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Favorites</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .container {
            padding: 20px;
            background-color: #fff;
            max-width: 1200px;
            margin: 0 auto;
        }

        .recipe-box {
            float: left;
            width: calc(33.33% - 20px);
            margin: 10px;
            padding: 15px;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            background: #fff;
            text-align: center;
        }

        .recipe-box img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }

        h1 {
            color: #16b978;
            text-align: center;
        }

        .button-primary {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 16px;
            background-color: #16b978;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
        }

        .button-primary:hover {
            background-color: #128a61;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Your Favorite Meals</h1>
        <?php if (!empty($favorites)): ?>
            <div class="clearfix">
                <?php foreach ($favorites as $meal): ?>
                    <div class="recipe-box">
                        <img src="uploads/<?php echo htmlspecialchars($meal['meal_image'] ?? 'default.jpg'); ?>" alt="<?php echo htmlspecialchars($meal['meal_name'] ?? 'Meal'); ?>">
                        <h3><?php echo htmlspecialchars($meal['meal_name'] ?? 'No Name'); ?></h3>
                        <p><?php echo htmlspecialchars($meal['meal_description'] ?? 'No description available'); ?></p>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($meal['category_name'] ?? 'Unknown Category'); ?></p>
                        <p><strong>Added:</strong> <?php echo getTimeElapsedString($meal['date_added'] ?? date('Y-m-d H:i:s')); ?></p>
                        <a href="meal_details.php?meal_id=<?php echo $meal['meal_id']; ?>" class="button-primary">View Meal</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>You have no favorite meals yet. Add some to your favorites!</p>
        <?php endif; ?>
    </div>
</body>

</html>