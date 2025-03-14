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
    $name = $conn->real_escape_string($_POST['name']);
    $surname = $conn->real_escape_string($_POST['surname']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $age = intval($_POST['age']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    $checkUser = $conn->query("SELECT * FROM users WHERE username = '$username'");
    if ($checkUser->num_rows > 0) {
        $error = "Username already exists.";
    } else {
        $conn->query("INSERT INTO users (username, name, surname, gender, age, password) VALUES ('$username', '$name', '$surname', '$gender', '$age', '$password')");
        $_SESSION['username'] = $username;
        header("Location: landing_page.php");
        exit();
    }
   

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Festival Movie Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
   
</head>
<body class="text-light">

<div class="container-reg ">
    <h1 class="text-center">Register</h1>
    
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
        <div class="mb-3">
            <label class="form-label">First Name</label>
            <input type="text" name="name" class="form-control w-100" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Surname</label>
            <input type="text" name="surname" class="form-control w-100" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-control w-100" required>
                <option value="" disabled selected></option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Age</label>
            <input type="number" name="age" class="form-control w-100" required>
        </div>
        <button type="submit" class="btn btn-success d-block mx-auto w-40">Register</button>
        <p class="text-center mt-3">Already have an account? <a href="landing_page.php">Log in</a></p>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
