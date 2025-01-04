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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
    <script>
        $(document).ready(function() {
            $(".alert").alert();
        });
    </script>
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


        .search-container {
            margin-top: 100px;
            padding: 10px;
            display: flex;
            align-items: center;
            border-radius: 15px;
            background-image: url('dishes.jpg');
            background-size: cover;
            background-position: center;
            width: 98%;
            height: 300px;
            margin-bottom: 15px;
        }


.search-container input[type="text"] {
    width: 400px;
    padding: 15px;
    border-radius: 10px;
    border: 1px solid #ccc;
    margin-right: 10px;
    font-size: 14px;
}

.search-container .btn {
    background-color: #f04e23;
    color: white;
    padding: 15px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    cursor: pointer;
}

.search-container .btn:hover {
    background-color: #d03a1e;
}
.recipe-box {
    box-sizing: border-box;
    flex: 0 0 calc(33.33% - 20px); /* Ensures 3 items in a row */
    margin: 10px;
    padding: 15px;
    border-radius: 15px;
    background: white;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
}

/* Ensure images fit properly */
.recipe-box img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 10px;
}

/* Styling for recipe title and description */
.recipe-box h2 {
    margin: 10px 0;
    font-size: 20px;
    font-weight: bold;
    color: #333;
}

.recipe-box p {
    font-size: 14px;
    margin: 5px 0;
    color: #555;
}

.recipe-box .btn {
    display: inline-block;
    padding: 8px  20px;
    background-color:darkred;
    color: white;
    text-decoration: none;
    border-radius: 20px;
    margin-top: 10px;
    font-size: 14px;
    max-width: fit-content;
}

.row {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
}
.container {
    margin-left: 270px; 
    padding: 20px;
    font-size: 16px;
    color: black;
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
    <?php foreach ($posts as $post): ?>
        <div class="recipe-box">
            <?php
            $mealId = $post['meal_id'];
            $imageStmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ?");
            $imageStmt->execute([$mealId]);
            $images = $imageStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($images)) {
                echo '<img src="' . $images[0]['image_link'] . '" alt="Recipe Image">';
            }
            ?>
            <h2><?php echo $post['meal_name']; ?></h2>
            <p><strong>Description:</strong> <?php echo substr($post['description'], 0, 100); ?>...</p>
            <p><strong>Views:</strong> <?php echo $post['views']; ?></p>
            <p><strong>Date:</strong> <?php echo getTimeElapsedString($post['date_created']); ?></p>
            <a href="admin_view_details.php?meal_id=<?php echo $post['meal_id']; ?>" class="btn">View Details</a>
        </div>
    <?php endforeach; ?>
</div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>