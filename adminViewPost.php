<?php

session_start();
date_default_timezone_set('Asia/Manila');
require("0conn.php");

if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("Location: 3login.php");
    exit();
}

$loggedInUsername = isset($_SESSION["username"]) ? $_SESSION["username"] : "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

function getTimeElapsedString($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->d == 0) {
        if ($diff->h == 0) {
            if ($diff->i == 0) {
                return 'Now';
            } else {
                return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
            }
        } else {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        }
    } elseif ($diff->d == 1) {
        return '1 day ago';
    } elseif ($diff->d < 7) {
        return $diff->d . ' days ago';
    } else {
        return $ago->format('F j, Y'); // Display actual date if more than 7 days
    }
}

// Retrieve posts from users with search functionality
if (isset($_GET['search'])) {
    $searchTerms = explode(' ', $_GET['search']);
    $placeholders = array_fill(0, count($searchTerms), 'm.meal_name LIKE ? OR m.meal_id IN (SELECT i.meal_id FROM ingredients i WHERE i.ingredient_name LIKE ?)');
    $whereClause = implode(' OR ', $placeholders);

    $sql = "SELECT m.*, AVG(r.rating_value) AS average_rating
            FROM meals m
            LEFT JOIN ratings r ON m.meal_id = r.meal_id
            WHERE $whereClause
            GROUP BY m.meal_id, m.date_created
            ORDER BY m.date_created DESC";

    $stmt = $pdo->prepare($sql);
    $params = [];
    foreach ($searchTerms as $term) {
        $term = '%' . $term . '%';
        $params[] = $term;
        $params[] = $term;
    }
    $stmt->execute($params);
} else {
    $stmt = $pdo->query("SELECT m.*, AVG(r.rating_value) AS average_rating FROM meals m LEFT JOIN ratings r ON m.meal_id = r.meal_id GROUP BY m.meal_id, m.date_created ORDER BY m.date_created DESC");
    $stmt->execute();
}

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home Page</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            padding-top: 70px; /* Adjusted for fixed navbar height */
            background-color: #f9f9f9;
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;

        }

        .navbar {
            background-color: #FFFF;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); /* Shadow */
            height: 80px; /* Increased navbar height */
        }

        .navbar-dark .navbar-nav .nav-link {
            color: #182c25 !important; /* Updated color for better visibility */
        }

        .navbar-toggler-icon {
            background-color: #16b978; /* Green color for the toggle icon */
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

        .search-container {
            margin-top: 20px;
            margin-bottom: 20px;
            width: 60%; /* Adjust the width as needed */
        }

        .container {
            flex-grow: 1;
            width: 100%;
            margin-top: 120px;
        }

        p {
            font-size: 16px;
            color: #000;
        }

        .search-container input {
            width: 100%;
        }

        .recipe-box {
     box-sizing: border-box;
            flex: 0 0 calc(33.33% - 20px); /* Updated width to ensure 3 items in a row */
            margin: 10px;
            padding: 10px;
            border-radius: 15px;
            background: white;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.4);
}

.recipe-card img {
    object-fit: cover;
    width: 100%;
    height: 230px; /* Updated height */
    object-fit: cover;
    border-radius: 10px;
}

.recipe-card-body h2 {
    margin-top: 0;
    margin-bottom: 2px;
    font-size: 20px; /* Adjusted font size */
}

.recipe-card-body h3 {
    margin-bottom: 2px;
    font-size: 16px; /* Adjusted font size */
}

.recipe-card-body p {
    margin: 5px 0;
    font-size: 14px; /* Adjusted font size */
    flex-grow: 1; /* Allow content to grow within the container */
}

.view-details-button {
    display: inline-block;
    padding: 8px 16px;
    background-color: #04AA6D; /* Darker green button color */
    color: white;
    text-decoration: none;
    border-radius: 4px;
    margin-top: 10px;
    font-size: 14px; /* Adjusted font size */
    align-self: flex-end; /* Align button to the bottom right */
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
        <div class="search-container">
            <form class="form-inline" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search" name="search" id="search"
                        value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>

        <div class="row">
            <?php
            foreach ($posts as $post):
                ?>
                <div class="col-md-4 recipe-box">
                    <div class="card recipe-card">
                        <?php
                        $mealId = $post['meal_id'];
                        $imageStmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ?");
                        $imageStmt->execute([$mealId]);
                        $images = $imageStmt->fetchAll(PDO::FETCH_ASSOC);

                        if (!empty($images)) {
                            echo '<img class="card-img-top" src="' . $images[0]['image_link'] . '" alt="Recipe Image">';
                        }
                        ?>
                        <div class="card-body recipe-card-body">
                            <h2 class="card-title"><?php echo $post['meal_name']; ?></h2>
                            <h3 class="card-text"><strong></strong><?php echo substr($post['description'], 0, 100); ?>....</h3>
                            <p class="card-text">Views: <?php echo $post['views']; ?></p>
                            <p class="card-text">Posted by: <?php echo $post['username']; ?></p>
                            <p class="card-text">Date: <?php echo getTimeElapsedString($post['date_created']); ?></p>
                            <a href="admin_view_details.php?meal_id=<?php echo $post['meal_id']; ?>"
                                class="btn btn-success">View Details</a>
                        </div>
                    </div>
                </div>
                <?php
            endforeach;
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
