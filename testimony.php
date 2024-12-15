<?php
session_start();
require("0conn.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['username'])) {
        header("Location: 3login.php");
        exit();
    }

    $username = $_SESSION['username'];
    $testimonial_text = $_POST['testimonial_text'];
    $testimonial_text = implode(' ', array_slice(str_word_count($testimonial_text, 2), 0, 100));

    try {
        $stmt = $conn->prepare("INSERT INTO testimonies (username, testimonial_text, date_posted) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $username, $testimonial_text);
        $stmt->execute();
        $stmt->close();
        header("Location: testimony.php");
        exit();
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
<title>eSangkap</title>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        display: flex;
        flex-wrap: wrap;
    }

    /* Logo Header */
    .logo-container {
        padding: 7px;
        position: fixed;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .logo-container img {
        height: 60px;
        width: auto;
        margin-right: 10px;
        border-radius: 50%;
    }

    .logo-title {
        color: #f04e23;
    
    }

    .sidebar {
        background-color: #f04e23;
        height: 100%;
        width: 250px;
        position: fixed;
        top: 70px; /* Adjust under the logo header */
        left: 0;
        overflow-x: hidden;
        padding-top: 20px;
        display: flex;
        flex-direction: column;
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

    .sidebar a i {
        margin-right: 15px;
    }
    .sidebar a.active {
            background-color:#ffcccb;
            color: darkred;
        }

    .sidebar a:hover {
        background-color: white;
        color: darkred;
    }

    .container {
        margin-left: 250px;
        margin-top: 190px; /* Adjust for header space */
        width: calc(100% - 250px);
        padding: 20px;
        box-sizing: border-box;
        text-align: center;
    }

    h1 {
        color: #f04e23;
        font-size: 2rem;
        margin-bottom: 20px;
    }

    form {
        max-width: 600px;
        margin: auto;
    }

    label {
        display: block;
        margin-bottom: 10px;
        font-size: 1.1rem;
    }

    textarea {
        width: 100%;
        padding: 15px;
        margin: 10px 0 20px;
        border: none;
        border-radius: 30px;
        background-color: #f4f4f4;
        resize: none;
        font-size: 1rem;
        outline: none;
    }

    input[type="submit"] {
        width: 100%; 
         padding: 15px;
            background-color: darkred;
            color: white;
            border: none;
            border-radius: 25px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
    }

    input[type="submit"]:hover {
        background-color: #e74c3c;
            box-shadow: 2px 4px 8px rgba(0, 0, 0, 0.2);
    }

</style>
</head>
<body>
<div class="logo-container">
    <img src="logo.jpg" alt="Logo">
    <h2><div class="logo-title">eSangkap</div></h2>
</div>
<div class="sidebar">
    <a href="9customer.php"><i class="fa fa-fw fa-home"></i> Home</a>
    <a href="favoritesreen.php"><i class="fas fa-heart"></i> Favorites</a>
    <a href="view_categories.php"><i class="fas fa-list"></i> Categories</a>
    <a href="12user_profile.php" class="active"><i class="fas fa-user"></i> Profile</a>
    <a href="about_us.php"><i class="fas fa-info-circle"></i> About Us</a>
    <a href="4logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="container">
    <h1>Write Testimonies</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="testimonial_text">Your Testimony (up to 100 words):</label>
        <textarea name="testimonial_text" placeholder="Write here..." rows="4" required></textarea>
        <input type="submit" value="Submit">
    </form>
</div>

</body>
</html>
