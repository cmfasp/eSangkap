<?php
session_start();
require("0conn.php");

if (isset($_GET['category_id'])) {
    $category_id = $_GET['category_id'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }

    // Retrieve meals in the selected category
    $stmt = $pdo->prepare("SELECT * FROM meals WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Redirect back to customer.php if category_id is not set
    header("Location: 9customer.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meals in Category</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f3f3;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        h1 {
            font-size: 24px;
            margin: 0;
        }

        h2 {
            font-size: 20px;
            margin: 10px 0;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            font-size: 16px;
            margin: 5px 0;
        }
        a {
            text-decoration: none;
            color: #007BFF;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Meals in Category</h1>
        <h2>Category: <?php echo $category_id; ?></h2>
        <ul>
            <?php foreach ($meals as $meal): ?>
                <li>
                    <a href="11meal_details_comments.php?meal_id=<?php echo $meal['meal_id']; ?>">
                        <?php echo $meal['meal_name']; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <p><a href="9customer.php">Back to Categories</a></p>
    </div>
</body>
</html>