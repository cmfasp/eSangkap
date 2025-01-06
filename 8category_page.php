<?php
session_start();
require("0conn.php");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if (isset($_GET["category_id"])) {
    $category_id = $_GET["category_id"];

    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($category) {
        $category_name = $category["category_name"];
    } else {
        $category_name = "Category Not Found";
    }
} else {
    $category_name = "Category Not Selected";
} 
if (isset($_GET['delete_id'])) {
    $meal_id = $_GET['delete_id'];

    $stmt = $pdo->prepare("DELETE FROM favorites WHERE meal_id = ?");
    $stmt->execute([$meal_id]);

    
    $stmt = $pdo->prepare("DELETE FROM ratings WHERE meal_id = ?");
    $stmt->execute([$meal_id]);

    $stmt = $pdo->prepare("DELETE FROM meals WHERE meal_id = ?");
    $stmt->execute([$meal_id]);

    header("Location: " . $_SERVER['PHP_SELF'] . "?category_id=" . $category_id);
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            background-color: #f04e23;
            ;
            margin-top: 65px;
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            overflow-x: hidden;
            padding-top: 30px;
            display: flex;
            flex-direction: column;
        }

        .logo-container {
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 50px;
            padding: 20px;
            width: auto;
            margin-right: 10px;
        }

        .logo-container {
            text-align: left;
            padding-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .title {
            color: #f04e23;
            font-size: 24px;
            font-weight: bold;
            text-align: left;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px 25px;
            text-decoration: none;
            font-size: 15px;
            text-align: left;
            display: flex;
            align-items: center;
        }

        .sidebar a:hover {
            background-color: white;
            color: darkred;
        }

        .sidebar a.active {
            background-color: #ffcccb;
            color: darkred;
        }

        .sidebar a i {
            margin-right: 15px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            margin-top: 100px;
            /* Reduce space at the top */
            margin-left: 400px;
        }

        h1.mb-3 {
            color: darkred;
            /* Change text color to dark red */
            font-size: 32px;
            /* You can adjust the size as needed */
        }


        .recipe-list {
            margin-top: 20px;
            font-size: 20px;
        }

        .mb-3 {
            margin-bottom: 20px;
        }


        .list-group-item {
            border-radius: 5px;
            font-size: 17px;
            background-color: rgb(255, 224, 224);
            margin-bottom: 10px;
            color: #000;
            border: 1px lightgray;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .delete-icon {
            color: darkred;
            cursor: pointer;
        }

        .delete-icon:hover {
            color: #c53b18;
            /* Darker color on hover */
        }
    </style>
</head>

<body>
    <div class="logo-container">
        <img src="logo.jpg" alt="Logo" class="logo">
        <h2 class="title">eSangkap</h2>
    </div>

    <div class="sidebar">
        <a class="nav-link" href="adminViewPost.php">
            <i class="fa fa-fw fa-home"></i>Home
        </a>
        <a class="nav-link" href="5admin.php">
            <i class="fa-solid fa-utensils"></i>Manage Recipe
        </a>
        <a class="nav-link" href="4logout.php">
            <i class="fas fa-fw fa-sign-out"></i>Logout
        </a>
    </div>
    <div class="container">
        <div class="card-header">
            <h1 class="mb-3"><?php echo $category_name; ?></h1> 
        </div>


        <div class="recipe-list">
            <p class="mb-3">Meals</p>

            <?php
            $stmt = $pdo->prepare("SELECT * FROM meals WHERE category_id = ?");
            $stmt->execute([$category_id]);
            $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($recipes) > 0) {
                echo '<div class="list-group">';
                foreach ($recipes as $recipe) {
                    echo '<div class="list-group-item">
                        ' . $recipe['meal_name'] . '
                        <a href="?category_id=' . $category_id . '&delete_id=' . $recipe['meal_id'] . '" class="delete-icon">
                            <i class="fa fa-trash"></i>
                        </a>
                      </div>';
                }
                echo '</div>';
            } else {
                echo "<p>No recipes found in this category.</p>";
            }
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>