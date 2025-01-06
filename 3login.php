<?php
session_start();

require("0conn.php");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_username = $_POST["username"];
    $entered_password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$entered_username]);
    $user = $stmt->fetch();

    if ($user && password_verify($entered_password, $user["password"])) {
        $_SESSION["username"] = $entered_username; // Use the entered username
        if ($entered_username === "admin" || $entered_username === "admin2") {
            $_SESSION["is_admin"] = true;
            header("Location: adminViewPost.php");
        } else {
            $_SESSION["is_admin"] = false;
            header("Location: 9customer.php");
        }
        exit();
    } else {
        $errors[] = "Invalid username or password. Please try again.";
    }
}


// Check if there's an error message in the session and display it as a pop-up
if (isset($_SESSION['error_message'])) {
    echo "<script type='text/javascript'>
            alert('" . $_SESSION['error_message'] . "');
          </script>";
    unset($_SESSION['error_message']);  // Clear the error message after displaying it
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #ededed;
            color: #000;
        }

        .container {
            display: flex;
            max-width: 800px; 
            margin: 100px auto;
            border-radius: 8px;
            overflow: hidden;
        }

        .left-panel {
            flex: 1;
            background: url('loginPhoto.jpg');
            background-size: cover;
            background-position: center;
            border-radius: 8px 0 0 8px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .right-panel {
            flex: 1;
            padding: 20px;
            border-radius: 0 8px 8px 0;
            background-color: #fff;
        }

        h2 {
            text-align: center;
            color: #000;
        }

        form {
            margin-top: 20px;
        }

        label {
            font-weight: bold;
            color: #000;
        }

        button {
            width: 100%;
            background-color: #fff;
            color: #000;
        }

        .register-link {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <div class="left-panel">
        </div>
        <div class="right-panel">
            <h2>Login</h2>
            <?php
            foreach ($errors as $error) {
                echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
            }
            ?>
            <form method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <button type="submit">Login</button>
            </form>
            <div class="register-link">
                <p>Don't have an account? <a href="1registration.php">Register</a></p>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.8/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>