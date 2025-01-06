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
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Check if the password and confirm password match
    if ($password != $confirm_password) {
        $errors[] = "Password and Confirm Password do not match.";
    }
    // Check if the password is at least 8 characters long
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    // Check for existing username and email
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    $existingUser = $stmt->fetchColumn();

    if ($existingUser > 0) {
        $errors[] = "Username or Email already exists. Please choose another.";
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password_hash]);

        header("Location: 3login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Registration</title>
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
            background: url('picture.jpg');
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

        .form-group {
            margin-bottom: 20px;
        }

        button {
            width: 100%;
            background-color: #000;
            color: #fff;
        }

        .btn {
            background-color: #fff;
            color: #000;
        }

        img {
            width: 100%;
            height: auto;
            max-height: 500px;
            filter: grayscale(100%);
            border-radius: 8px;
        }

        .login-link {
            margin-top: 20px;
            text-align: center;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel"></div>
        <div class="right-panel">
            <h2>Registration</h2>
            <?php
            foreach ($errors as $error) {
                echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
            }
            ?>
            <form action="1registration.php" method="post" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            <div class="login-link">
                <p>Already have an account? <a href="3login.php">Login</a></p>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.8/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
