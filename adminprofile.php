<?php
session_start();
require("0conn.php");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if (!isset($_SESSION['username'])) {
    header("Location: 1login.php");
    exit();
}

$username = $_SESSION['username'];

$stmt = $pdo->prepare("SELECT * FROM meals WHERE username = ? ORDER BY date_created DESC");
$stmt->execute([$username]);
$userRecipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #ededed;
            margin: 0 auto;
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
        }


        h3 {
            margin-top: 70px;
            color: #18392B;
            font-weight: bold;
            margin-left: 350px;
        }

        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 150px;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }

        img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            justify-items: center;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: none;
            color: #0056b3;
        }


        .btn-secondary {
            background-color: #4caf50;
            color: #fff;
        }

        li,
        h2 {
            color: #4caf50;
        }

        p {
            color: #3e3e36;
            font-size: 15px;
        }

        .image-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .image-container img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            justify-items: center;
        }

        h1 {
            font-size: 25px;
            justify-content: center;
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
            height: 90px;
        }

        .topnav {
            background-color: #16b978;
            overflow: hidden;
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            padding-top: 90px;
            transition: top 0.3s;
            z-index: 1; /* Set a lower z-index */
        }

        .topnav a {
            color: #f2f2f2;
            text-align: center;
            padding: 15px 25px;
            text-decoration: none;
            font-size: 17px;
            display: inline-block;
        }

        .topnav a:hover {
            background-color: #04AA6D;
            color: white;
        }

        .topnav a.active {
            background-color: #04AA6D;
            color: white;
        }

        .topnav a i {
            margin-right: 10px;
        }
        .logo {
            display: flex;
            align-items: center;
        }
        .logo h1{
            color: #16b978;
        }

        .logo img {
            width: 40px; /* Adjust the width as needed */
            margin-right: 10px;
        }
    </style>
</head>

<body>

<div class="logo-container">
        <div class="logo">
            <img src="logo.png" alt="Tastebud Logo">
            <h1>Tastebud</h1>
        </div>
    </div>

    <div class="topnav">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="nav-link" href="adminViewPost.php">
            <i class="fa fa-fw fa-home"></i>Home
        </a>
        <a class="nav-link" href="adminprofile.php">
            <i class="fas fa-fw fa-user"></i>Admin Profile
        </a>
        <a class="nav-link" href="5admin.php">
            <i class="fa-solid fa-utensils"></i>Manage Recipe
        </a>
        <a class="nav-link" href="4logout.php">
            <i class="fas fa-fw fa-sign-out"></i>Logout
        </a>
    </div>


    <div class="container">
        <ul>
            <?php foreach ($userRecipes as $recipe) { ?>
                <li>
                    <h2><?php echo $recipe['meal_name']; ?></h2>
                    <p>Category: <?php echo getCategoryName($pdo, $recipe['category_id']); ?></p>
                    <p>Video Link: <a href="<?php echo $recipe['video_link']; ?>" target="_blank">Watch Video</a></p>

                    <div class="image-container">
                        <?php
                        $meal_id = $recipe['meal_id'];
                        $images = getImages($pdo, $meal_id);
                        if (!empty($images)) {
                            echo "<p> <img src='{$images[0]['image_link']}' alt='Recipe Image'></p>";
                        }
                        ?>
                    </div>

                    <div class="date"><p>Date Created: <?php echo $recipe['date_created']; ?></p></div>
                    <p><a href="adminposts.php?meal_id=<?php echo $recipe['meal_id']; ?>">View Details</a></p>
                </li>
            <?php } ?>
        </ul>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

<?php
function getCategoryName($pdo, $category_id)
{
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    return $category ? $category['category_name'] : 'Unknown';
}

function getImages($pdo, $meal_id)
{
    $stmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ?");
    $stmt->execute([$meal_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
