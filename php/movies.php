<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Movie Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-dark text-light">

<div class="container mt-5">
    <a href="index.php" class="btn btn-light mb-3">⬅ Back to Search</a>

    <?php
    $servername = "db";
    $username = "user";
    $password = "password";
    $database = "movielens";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
    }

    if (isset($_GET['movieId'])) {
        $movieId = $conn->real_escape_string($_GET['movieId']);
        
        $sql = "SELECT * FROM movies WHERE movieId = '$movieId'";
        $result = $conn->query($sql);
        

        if ($result->num_rows > 0) {
            $movie = $result->fetch_assoc();
            $boxOffice = isset($movie['boxOffice']) ? preg_replace('/[^0-9]/', '', $movie['boxOffice']) : 0;
            $formattedBoxOffice = $boxOffice ? "$" . number_format((int)$boxOffice, 0, '.', ',') : "N/A";
        
            echo "<div class='card mx-auto' style='max-width: 600px;'>
            <img src='{$movie['posterUrl']}' class='card-img-top' alt='{$movie['title']}'>
            <div class='card-body'>
                <h2 class='card-title'>{$movie['title']}</h2>
                <p class='card-text'><strong>IMDB Rating:</strong> ⭐ {$movie['imdbRating']}</p>
                <p class='card-text'><strong>Genre:</strong> {$movie['genres']}</p>
                <p class='card-text'><strong>Director:</strong> {$movie['directors']}</p>
                <p class='card-text'><strong>Actors:</strong> {$movie['actors']}</p>
                <p class='card-text'><strong>Box Office: 💵</strong> {$formattedBoxOffice}</p>
                <p class='card-text'><strong>Runtime:</strong> {$movie['runtime']}</p>
                <p class='card-text'><strong>Language:</strong> {$movie['language']}</p>
            </div>
        </div>";
} else {
            echo "<div class='alert alert-warning text-center'>Movie details not found.</div>";
        }
    }

    $conn->close();
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
