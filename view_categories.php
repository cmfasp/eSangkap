<?php
require("0conn.php");
$sql = "SELECT category_id, category_name FROM categories";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $categories = [];
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

        .container {
            width: 100%;
            min-height: 100vh;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex-direction: column;
            align-items: center;
            text-align: center;
            overflow-y: auto;
        }

        h2 {
            font-size: 24px;
            margin-left: 60px;
            margin-top: 160px;
            margin-bottom: 20px;
            color: black;
        }

        h3 {
            color: #16b978;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        ul li {
            margin: 10px 0;
        }

        .meals-container {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
        }

        .meal-name {
            font-size: 18px; 
            display: block;
            margin: 10px 0; 
            color: #16b978;
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
        
        .container {
            flex-grow: 1;
            margin-top: 120px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex-direction: column;
            align-items: center;
            text-align: center;
            height: 78vh;
            
        }

        form {
            padding: 20px;
            width: 50%;
            margin: 0 auto;
            box-sizing: border-box;
            text-align: center;
        }

        label {
            font-size: 18px;
            margin-right: 10px;
        }

        select {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            margin-left: 10px;
            background-color: #f2f2f2;
            border: none;
            box-sizing: border-box;
            margin-top: 15px;
        }

        input[type="submit"] {
            padding: 10px 16px;
            font-size: 16px;
            background-color: #16b978;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .topnav a.active {
                background-color: lightgray;
                color: black;
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
        
        <a href="12user_profile.php"> <i class="fas fa-fw fa-user"></i>Profile</a>
        <a href="view_categories.php"<?php echo (basename($_SERVER['PHP_SELF']) == 'view_categories.php') ? 'class="active"' : ''; ?>><i class="fa-solid fa-list"></i>Categories</a>
        <a href="9customer.php"><i class="fa-solid fa-utensils"></i>User Recipes</a>
        <a href="14chat.php"><i class="fa-solid fa-comment"></i>Chat</a>
        <a href="4logout.php"><i class="fas fa-fw fa-sign-out"></i> Logout</a>
    </div>

    <div class="container">
        <form action="process_form.php" method="post">
            <label for="category">Select a category:</label>
            <select name="category" id="category">
                <?php
                foreach ($categories as $category) {
                    echo "<option value='{$category['category_id']}'>{$category['category_name']}</option>";
                }
                ?>
            </select>
            <input type="submit" value="View Category">
        </form>
    </div>
</body>
</html>