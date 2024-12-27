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
    $placeholders = array_fill(0, count($searchTerms), 'm.meal_name LIKE ? OR m.meal_id IN (SELECT i.meal_id FROM ingredients i WHERE i.ingredient_name LIKE ?)');
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

function getCategoryName($pdo, $category_id)
{
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    return $category ? $category['category_name'] : 'Unknown';
}

function getTimeElapsedString($datetime)
{
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            /* Align logo and text in a row */
            align-items: center;
            /* Vertically align the logo and text */
        }

        .logo {
            width: 60px;
            /* Circular logo size */
            height: 60px;
            /* Make the height same as width to make it circular */
            border-radius: 50%;
            /* This makes the image circular */
            object-fit: cover;
            /* Ensures the image is properly scaled inside the circle */
            margin-right: 10px;
            /* 10px space between the logo and the text */
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
            margin-left: 250px;
            padding: 20px;
            background-color: #fff;
        }

        .search-container {
            margin-top: 100px;
            padding: 20px;
            border-radius: 15px;
            display: flex;
            background-image: url('dishes.jpg');
            align-items: center;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .search-container input {
            padding: 12px;
            font-size: 16px;
            border-radius: 10px;
            border: 1px solid #f3b3a6;
            flex: 1;
            margin-right: 10px;
            background-color: #fff;
        }

        .search-container button {
            padding: 12px 20px;
            font-size: 16px;
            background-color: #f04e23;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }

        .search-container button:hover {
            background-color: #c53b18;
        }


        /* Main content area */
        .container {
            margin-left: 250px;
            /* Adjust according to the sidebar width */
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


        .search-container {
            margin-top: 100px;
            padding: 10px;
            display: flex;
            align-items: center;
            border-radius: 15px;
            background-image: url('dishes.jpg');
            /* Replace with your image */
            background-size: cover;
            background-position: center;
            width: 100%;
            height: 300px;
        }

        .search-container .left-text {
            color: white;
            font-size: 30px;
            font-weight: bold;
            max-width: 50%;
            font-family: 'Poppins', sans-serif;
            margin-left: 20px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
        }

        .search-container .right-section {
            display: flex;
            align-items: center;
        }

        .search-container input {
            font-size: 16px;
            border-radius: 10px;
            border: 1px whitesmoke;
            flex: 1;
            margin-left: 100px;
            margin-right: 2px;
            background-color: white;
            /* White background with opacity */
            color: black;
            /* White text inside the search box */
        }

        /* Search Button */
        .search-container .search-form button {
            padding: 12px 20px;
            font-size: 16px;
            background-color: #f04e23;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin-left: 10px;
            /* Space between input and button */
        }

        .search-container .search-form button:hover {
            background-color: #c53b18;
        }

        /* Sort Dropdown */
        .search-container .sort-form select {
            padding: 12px;
            font-size: 16px;
            border-radius: 10px;
            margin-left: 10px;
            border: white 1px;
        }

        /* Sort Button */
        .search-container .sort-form button {
            padding: 12px 20px;
            font-size: 16px;
            background-color: #f04e23;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin-left: 10px;
        }

        .search-container .sort-form button:hover {
            background-color: #c53b18;
        }

        .view-details-button {
            padding: 8px 16px;
            background-color: darkred;
            color: #fff;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            margin-top: 10px;
        }

        .topnav a.active {
            background-color: lightgray;
            color: black;
        }

        .views {
            color: gray;
            font-size: 15px;
        }

        .meal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .favorite-button {
            background-color: #ffcccb;
            color: darkred;
            font-size: 16px;
            text-decoration: none;
            padding: 8px 10px;
            border-radius: 25px;
        }

        .favorite-button:hover {
            background-color: darkred;
            color: white;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="logo-container">
            <img src="logo.jpg" alt="Logo" class="logo">
            <h2 class="title">eSangkap</h2>
        </div>

        <?php if (isset($_SESSION['username'])) : ?>
            <a href="9customer.php" <?php echo (basename($_SERVER['PHP_SELF']) == '9customer.php') ? 'class="active"' : ''; ?>>
                <i class="fa fa-fw fa-home"></i>Home
            </a>
            <a href="favoritescreen.php">
                <i class="fa-solid fas fa-heart"></i>Favorites
            </a>
            <a href="view_categories.php">
                <i class="fa-solid fa-list"></i>Categories
            </a>
            <a href="12user_profile.php">
                <i class="fas fa-fw fa-user"></i>Profile
            </a>
            <a href="about_us.php">
                <i class="fa-solid fa-info-circle"></i>About Us
            </a>
            <a href="4logout.php"><i class="fas fa-fw fa-sign-out"></i>Logout
            </a>
        <?php else : ?>
            <a href="1registration.php">
                <i class="fa fa-fw fa-home"></i>Home
            </a>
            <a href="about_us.php">
                <i class="fa-solid fa-info-circle"></i>About Us
            </a>
            <a href="favoritescreen.php">
                <i class="fa-solid fas fa-heart"></i>Favorites
            </a>
            <a href="1registration.php">
                <i class="fas fa-fw fa-user"></i>Categories
            </a>
            <a href="1registration.php" <?php echo (basename($_SERVER['PHP_SELF']) == '12user_profile.php') ? 'class="active"' : ''; ?>>
                <i class="fa fa-fw fa-home"></i>Profile
            </a>
            <a href="4logout.php"><i class="fas fa-fw fa-sign-out"></i>Logout
            </a>
        <?php endif; ?>
    </div>


    <div class="container">
        <div class="search-container">
            <div class="left-text">
                All best recipes in one place. Upload your own home recipe.
            </div>

            <div class="right-section">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" class="search-form">
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
        </div>

        <h2>Welcome, <?php echo htmlspecialchars($loggedInUsername); ?>!</h2>
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
                    $meal_id = $recipe['meal_id'];
                    $imageStmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ? LIMIT 1");
                    $imageStmt->execute([$meal_id]);
                    $firstImage = $imageStmt->fetch(PDO::FETCH_ASSOC);

                    if ($firstImage) {
                        echo '<img src="' . $firstImage['image_link'] . '" style="max-width: 100%;">';
                    }
                    ?>
                    <?php
                    $favStmt = $pdo->prepare("SELECT * FROM favorites WHERE meal_id = ? AND username = ?");
                    $favStmt->execute([$meal_id, $loggedInUsername]);
                    $isFavorite = $favStmt->rowCount() > 0;
                    ?>

                    <div class="meal-header">
                        <h2><?php echo $recipe['meal_name']; ?></h2>
                        <?php if ($isFavorite): ?>
                            <button class="favorite-button" disabled>
                                <i class="fas fa-heart"></i> Already in Favorites
                            </button>
                        <?php else: ?>
                            <a href="add_to_favorites.php?meal_id=<?php echo $meal_id; ?>" class="favorite-button">
                                <i class="fas fa-heart"></i> Favorite
                            </a>
                        <?php endif; ?>
                    </div>
                    <p><strong><?php echo $recipe['username']; ?></strong></p>
                    <?php
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

    <script>
        $(document).ready(function() {
            $(".favorite-button").click(function() {
                var meal_id = $(this).data("meal-id");


                $.ajax({
                    url: 'add_to_favorite.php',
                    type: 'GET',
                    data: {
                        meal_id: meal_id
                    },
                    success: function(response) {
                        if (response === "success") {
                            alert("Successfully added to favorites!");
                        } else {
                            alert("Error: " + response);
                        }
                    },

                });
            });
        });
    </script>
</body>

</html>
<?php echo "Meal ID: " . $meal_id; ?>