<?php
session_start();
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

// Fetch categories before checking form submissions
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Check if the form is submitted to add a new category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["category_name"])) {
    $category_name = $_POST["category_name"];
    $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (?)");
    $stmt->execute([$category_name]);

    // Set success message for category addition
    $_SESSION["category_added"] = true;

    // Redirect to prevent form resubmission on page refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Check if the form is submitted to delete selected categories
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_selected"])) {
    if (isset($_POST["selected_categories"]) && is_array($_POST["selected_categories"])) {
        $selectedCategories = $_POST["selected_categories"];
        foreach ($selectedCategories as $categoryId) {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
            $stmt->execute([$categoryId]);
        }

        // Set success message for category deletion
        $_SESSION["category_deleted"] = true;

        // Fetch updated categories after deletion
        $categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
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

     
        .list-group {
            text-align: left;
        }

        li {
            font-size: 18px;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        a {
            text-decoration: none;
            color: black;
        }

      
        .btn-margin {
            margin-top: 20px;
        }

        h2 {
            padding: 20px;
        }

        h3 {
            color: #000;
            padding: 10px;
            margin-left: 20px;
        }

        .add {
            margin: 20px auto;
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            margin-top: 60px;
        }

        .categories {
            margin: 20px auto;
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            margin-top: 5px;
        }

        .add-recipe-btn {
    padding: 8px 16px;
    background-color: #f04e23;
    color: #fff;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
}

/* Remove hover effect */
.add-recipe-btn:hover {
    background-color: #f04e23; /* Keep the same background color */
    color: #fff; /* Keep the same text color */
    text-decoration: none;
}

        .delete-btn {
    padding: 8px 16px;
    background-color: #f04e23; /* orange color */
    color: #fff;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
}



        .container {

            margin-top: 150px;
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
        <?php if (isset($_SESSION["category_added"]) && $_SESSION["category_added"]): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Category added successfully!
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION["category_added"]); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION["category_deleted"]) && $_SESSION["category_deleted"]): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Category deleted successfully!
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION["category_deleted"]); ?>
        <?php endif; ?>

        <div class="add">
            <p>Add Category</p>
            <form method="post">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="category_name" placeholder="Category Name" required>
                    <div class="input-group-append">
                        <button class="btn btn-outline-primary" type="submit">Add Category</button>
                    </div>
                </div>
            </form>
        </div>

   
            <p>Manage Recipes</p>
            <p><a href="6add_recipe.php" class="add-recipe-btn">Add New Recipe</a></p>


        <div class="categories">
            <p>Categories</p>
            <form method="post" id="deleteForm">
                <ul class="list-group">
                    <?php foreach ($categories as $category): ?>
                        <li class="list-group-item">
                            <input type="checkbox" id="category_<?php echo $category['category_id']; ?>" name="selected_categories[]" value="<?php echo $category['category_id']; ?>">
                            <span>
                                <a href="8category_page.php?category_id=<?php echo $category['category_id']; ?>">
                                    <?php echo $category['category_name']; ?>
                                </a>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <button type="submit" name="delete_selected" class="delete-btn mt-3" onclick="deleteSelectedCategories()">Delete Selected</button>

            </form>
        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>