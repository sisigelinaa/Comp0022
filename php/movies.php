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

    if (isset($_GET['id'])) {
        $imdbID = $_GET['id'];
        $apiKey = "973a80b2"; // Replace with your OMDB API Key
        $url = "http://www.omdbapi.com/?i=" . urlencode($imdbID) . "&apikey=" . $apiKey;
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if ($data && $data['Response'] == "True") {
            echo "<div class='card mx-auto' style='max-width: 600px;'>
                    <img src='{$data['Poster']}' class='card-img-top' alt='{$data['Title']}'>
                    <div class='card-body'>
                        <h2 class='card-title'>{$data['Title']} ({$data['Year']})</h2>
                        <p class='card-text'><strong>IMDB Rating:</strong> ⭐ {$data['imdbRating']}</p>
                        <p class='card-text'><strong>Genre:</strong> {$data['Genre']}</p>
                        <p class='card-text'><strong>Director:</strong> {$data['Director']}</p>
                        <p class='card-text'><strong>Actors:</strong> {$data['Actors']}</p>
                        <p class='card-text'><strong>Plot:</strong> {$data['Plot']}</p>
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
