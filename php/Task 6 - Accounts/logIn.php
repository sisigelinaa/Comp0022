<?php
session_start();
$servername = "db";
$username = "user";
$password = "password";
$database = "movielens";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $query = $conn->query("SELECT * FROM users WHERE username = '$username'");
    if ($query->num_rows > 0) {
        $user = $query->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            header("Location: accountPage.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Festival Movie Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
    <style>
        body {
            background: rgba(0, 0, 0, 0.8);
            background-size: cover;
        }

        .container {
            max-width: 400px;
            margin-top: 100px;
            background: rgba(0, 0, 0, 0.8);
            padding: 30px;
            border-radius: 10px;
        }

        h1 {
            color: #ffcc00;
            text-shadow: 2px 2px 10px rgba(255, 204, 0, 0.8);
        }

        .small-btns a {
            display: inline-block;
            width: auto;
            margin-top: 5px;
        }
    </style>
</head>

<body class="text-light">

    <div class="container">
        <h1 class="text-center">Log In</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"> <?= $error ?> </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control w-100" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control w-100" required>
            </div>
            <button type="submit" class="btn btn-primary d-block mx-auto w-40">Log In</button>
        </form>

        <div class="text-center mt-3 small-btns">
            <a href="../Task 1 - Dashboard/dashboard.php" class="btn btn-outline-light btn-sm"
                style="background-color: rgb(192, 177, 119);">Browse as Guest</a>
            <a href="register.php" class="btn btn-success btn-sm">Register</a>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>