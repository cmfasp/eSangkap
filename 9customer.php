<?php
session_start();
date_default_timezone_set('Asia/Manila');
require("0conn.php");

$loggedInUsername = isset($_SESSION["username"]) ? $_SESSION["username"] : "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if (isset($_GET['sort'])) {
    $sortOption = $_GET['sort'];

    switch ($sortOption) {
        case 'most_viewed':
            $orderBy = 'ORDER BY views DESC, m.date_created DESC';
            break;
        case 'most_rated':
            $orderBy = 'ORDER BY AVG(r.rating_value) DESC, m.date_created DESC';
            break;
        default:
            $orderBy = 'ORDER BY m.date_created DESC';
            break;
    }
} else {
    $orderBy = 'ORDER BY m.date_created DESC';
}

if (isset($_GET['search'])) {
    $searchTerms = explode(' ', $_GET['search']);
    $placeholders = array_fill(0, count($searchTerms), 'meal_name LIKE ? OR meal_id IN (SELECT meal_id FROM ingredients WHERE ingredient_name LIKE ?)');
    $whereClause = implode(' OR ', $placeholders);

    $sql = "SELECT m.*, AVG(r.rating_value) AS average_rating
            FROM meals m
            LEFT JOIN ratings r ON m.meal_id = r.meal_id
            WHERE $whereClause
            GROUP BY m.meal_id, m.date_created
            $orderBy";

    $stmt = $pdo->prepare($sql);
    $params = [];
    foreach ($searchTerms as $term) {
        $term = '%' . $term . '%';
        $params[] = $term;
        $params[] = $term;
    }
    $stmt->execute($params);
} else {
    $stmt = $pdo->query("SELECT m.*, AVG(r.rating_value) AS average_rating FROM meals m LEFT JOIN ratings r ON m.meal_id = r.meal_id GROUP BY m.meal_id, m.date_created $orderBy");
    $stmt->execute();
}

$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getCategoryName($pdo, $category_id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    return $category ? $category['category_name'] : 'Unknown';
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png">
    <style>
        body {
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f3f3;
            display: flex;
            flex-wrap: wrap;
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
        }

        .topnav a {
            float: center;
            color: #f2f2f2;
            text-align: center;
            padding: 15px 25px;
            text-decoration: none;
            font-size: 17px;
            display: flex;
            align-items: center;
        }

        .topnav a:hover {
            background-color: #ddd;
            color: black;
        }

        .topnav a.active {
            background-color: #04AA6D;
            color: white;
        }

        .topnav a i {
            margin-right: 30px;
        }

        .container {
            flex-grow: 1;
            background-color: #fff;
            width: 100%;
        }

        .recipe-box {
            box-sizing: border-box;
            float: left;
            padding: 10px;
            border-radius: 15px;
            background: white;
            margin: 10px;
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
            margin-left: 60px;
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

        .logo h1 {
            font-family: cursive;
            font-size: 24px;
            margin: 0;
            color: #16b978;
        }

        .search-container {
            margin-top: 80px;
            padding: 20px;
            margin-bottom: 10px;
            border-radius: 20px;
            width: 90%;
            box-sizing: border-box;
            display: flex;
            align-items: center;
        }

        .sort-form button {
            padding: 10px 16px;
            font-size: 16px;
            background-color: #16b978;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 70px;
            margin-left: 2px;
        }

        .sort-form select {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            background-color: #f2f2f2;
            border: none;
            box-sizing: border-box;
            margin-top: 70px;
            margin-left: 7px;
    }
        .search-container input {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            padding-left: 12px;
            padding-right: 450px;
            margin-left: 300px;
            background-color: #f2f2f2;
            border: none;
            box-sizing: border-box;
            margin-top: 70px;
        }

        .search-container button {
            padding: 10px 16px;
            font-size: 16px;
            background-color: #16b978;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .view-details-button{
            padding: 8px 16px;
            background-color: #04AA6D;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            margin-top: 10px;
        }
        .topnav a.active {
            background-color: lightgray;
            color: black;
        }
        .views{
            color: gray;
            font-size: 15px;
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
        <?php if (isset($_SESSION['username'])) : ?>
            <a href="12user_profile.php"><i class="fas fa-fw fa-user"></i>Profile</a>
            <a href="view_categories.php"><i class="fa-solid fa-list"></i>Categories</a>
            <a href="9customer.php"<?php echo (basename($_SERVER['PHP_SELF']) == '9customer.php') ? 'class="active"' : ''; ?>><i class="fa-solid fa-utensils"></i>User Recipes</a>
            <a href="14chat.php"><i class="fa-solid fa-comment"></i>Chat</a>
            <a href="4logout.php"><i class="fas fa-fw fa-sign-out"></i> Logout</a>
        <?php else : ?>
            <a href="1registration.php"<?php echo (basename($_SERVER['PHP_SELF']) == '12user_profile.php') ? 'class="active"' : ''; ?>>
                <i class="fa fa-fw fa-home"></i>Profile</a>
            <a href="1registration.php"><i class="fas fa-fw fa-user"></i>Categories</a>
            <a href="1registration.php"><i class="fa-solid fa-utensils"></i>User Recipes</a>
            <a href="1registration.php"><i class="fa-solid fa-comment"></i>Chat</a>
        <?php endif; ?>
    </div>

    <div class="container">
    <div class="search-container">
    <form action="" method="GET" class="search-form">
        <input type="text" placeholder="Search" name="search" id="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
        <button type="submit">Search</button>
    </form>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" class="sort-form">
        <select name="sort" id="sort">
            <option value="latest" <?php echo empty($_GET['sort']) || $_GET['sort'] === 'latest' ? 'selected' : ''; ?>>Latest</option>
            <option value="most_viewed" <?php echo isset($_GET['sort']) && $_GET['sort'] === 'most_viewed' ? 'selected' : ''; ?>>Most Viewed</option>
            <option value="most_rated" <?php echo isset($_GET['sort']) && $_GET['sort'] === 'most_rated' ? 'selected' : ''; ?>>Most Rated</option>
        </select>
        <button type="submit" id="sort-submit">Sort</button>
    </form>
</div>

<h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
        <div class="clearfix">
            <?php
            $counter = 0;
            foreach ($recipes as $recipe) {
                if ($counter % 3 == 0) {
                    echo '<div class="clearfix"></div>';
                }
            ?>
            <div class="recipe-box">
                <?php
                // Fetch the first image link associated with the meal_id from meal_images table
                $meal_id = $recipe['meal_id'];
                $imageStmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ? LIMIT 1");
                $imageStmt->execute([$meal_id]);
                $firstImage = $imageStmt->fetch(PDO::FETCH_ASSOC);

                if ($firstImage) {
                    echo '<img src="' . $firstImage['image_link'] . '" style="max-width: 100%;">';
                }
                ?>
                <h2><?php echo $recipe['meal_name']; ?></h2>
                <p><strong> <?php echo $recipe['username']; ?></p></strong>
                <?php
                    // Get the first 100 characters of the description
                    $description = strlen($recipe['description']) > 100 ? substr($recipe['description'], 0, 100) . '...' : $recipe['description'];
                    echo '<p><b>Description: </b>' . $description . '</p>';
                ?>

                <p>Views: <?php echo ($recipe['views']); ?></p>

                <p>Date: <?php echo getTimeElapsedString($recipe['date_created']); ?></p>
                <p><a class="view-details-button" href="<?php echo isset($_SESSION['username']) ? '11meal_details_comments.php?meal_id=' . $recipe['meal_id'] : '1registration.php'; ?>">View Details</a></p>
            </div>
            <?php
                $counter++;
            }
            ?>
        </div>
    </div>
</body>
</html>
