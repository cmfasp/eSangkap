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
            text-align: left;
            padding-bottom: 20px;
            display: flex;
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

        .logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .title {
            color: #f04e23;
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
            margin-bottom: 10px;
        }

        .recipe-box img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 10px;
        }

        h1 {
            margin-top: 100px;
            color: #c53b18;
            text-align: left;
        }


      
        form {
            padding: 20px;
            width: 50%;
            margin: 100px auto; /* Centers the form */
            box-sizing: border-box;
            text-align: center;
        }

        label {
            font-size: 17px;
            margin-right: 10px;
        }

        select {
            padding: 10px;
            font-size: 15px;
            border-radius: 5px;
            margin-left: 10px;
            background-color: #f2f2f2;
            border: none;
            box-sizing: border-box;
            margin-top: 15px;
            width: 30%; 
            font-family: 'Poppins', sans-serif;
        }

        input[type="submit"] {
            font-family: 'Poppins', sans-serif;
            padding: 10px 16px;
            font-size: 15px;
            background-color: #f04e23;
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 20px; 
        }


    </style>
</head>
<body>
<div class="logo-container">
        <img src="logo.jpg" alt="Logo" class="logo">
        <h2 class="title">eSangkap</h2>
    </div>


    <div class="sidebar">
        <a href="9customer.php"><i class="fa fa-fw fa-home"></i>Home</a>
        <a href="favoritescreen.php"><i class="fa-solid fas fa-heart"></i>Favorites</a>
        <a href="view_categories.php"class="active"><i class="fa-solid fa-list"></i>Categories</a>
        <a href="12user_profile.php"><i class="fas fa-user"></i>Profile</a>
        <a href="4logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
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