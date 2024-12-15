<?php
session_start();
require('0conn.php'); // Include your database connection file

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo "Please log in to view your favorite meals.";
    exit;
}

$username = $_SESSION['username'];  // Get username from session

// Query to retrieve the favorite meals along with meal information
$sql = "SELECT m.*, f.date_added
        FROM favorites f
        JOIN meals m ON f.meal_id = m.meal_id
        WHERE f.username = ?
        ORDER BY f.date_added DESC"; // Sort by date added to favorites

$stmt = mysqli_prepare($conn, $sql);

if ($stmt === false) {
    echo "Error preparing the query: " . mysqli_error($conn);
    exit;
}

// Bind parameters and execute the query
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);

// Store the result
$result = mysqli_stmt_get_result($stmt);

// Fetch all meals from the result
$favorites = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Close statement
mysqli_stmt_close($stmt);

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 20px;
            background-color: #fff;
        }
        .recipe-box {
            box-sizing: border-box;
            float: left;
            padding: 10px;
            border-radius: 15px;
            background: white;
            margin: 10px;
            justify-content: space-evenly;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
            width: calc(33.33% - 20px);
            box-sizing: border-box;
        }
        .recipe-box img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 10px;
        }
        h1 {
            font-size: 24px;
            margin-top: 20px;
            margin-bottom: 40px;
            color: #16b978;
        }
        .button-primary {
            background-color: #16b978;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .button-primary:hover {
            background-color: #128a61;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
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
                    <img src="uploads/<?php echo $meal['meal_image']; ?>" alt="<?php echo htmlspecialchars($meal['meal_name']); ?>">
                    <h3><?php echo htmlspecialchars($meal['meal_name']); ?></h3>
                    <p><?php echo htmlspecialchars($meal['meal_description']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars(getCategoryName($pdo, $meal['category_id'])); ?></p>
                    <p><strong>Added:</strong> <?php echo getTimeElapsedString($meal['date_added']); ?></p>
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
