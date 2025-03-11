<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Movie Info Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-dark text-light">

<div class="container mt-4">
    <h1 class="text-center">🎬 Movie Finder</h1>

    <!-- Search Form -->
    <form method="GET" class="d-flex justify-content-center my-4">
        <input type="text" name="movie" class="form-control w-50" placeholder="Search for a movie..." required>
        <button type="submit" class="btn btn-danger ms-2">Search</button>
    </form>

    <?php
    $servername = "db";
    $username = "user";
    $password = "password";
    $database = "movielens";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
    }

    if (isset($_GET['movie'])) {
        $movie = $_GET['movie'];
        $apiKey = "973a80b2"; // Replace with your OMDB API Key
        $url = "http://www.omdbapi.com/?s=" . urlencode($movie) . "&apikey=" . $apiKey;
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if ($data && $data['Response'] == "True") {
            echo "<div class='row row-cols-2 row-cols-md-4 g-4'>";
            foreach ($data['Search'] as $movie) {
                $title = $movie['Title'];
                $year = $movie['Year'];
                $poster = ($movie['Poster'] != "N/A") ? $movie['Poster'] : "no-image.jpg";
                $imdbID = $movie['imdbID'];

                echo "<div class='col'>
                        <a href='movies.php?id=$imdbID' class='text-decoration-none'>
                            <div class='card'>
                                <img src='$poster' class='card-img-top' alt='$title'>
                                <div class='card-body text-center'>
                                    <h6 class='text-light'>$title ($year)</h6>
                                </div>
                            </div>
                        </a>
                    </div>";
            }
            echo "</div>";
        } else {
            echo "<div class='alert alert-warning text-center'>No movies found.</div>";
        }
    } else {
        // Show recent movies from the database
        $result = $conn->query("SELECT * FROM movies ORDER BY id DESC LIMIT 8");

        if ($result->num_rows > 0) {
            echo "<h2 class='text-center mt-5'>🎥 Recently Searched Movies</h2>";
            echo "<div class='row row-cols-2 row-cols-md-4 g-4'>";
            while ($row = $result->fetch_assoc()) {
                echo "<div class='col'>
                        <a href='movie.php?id={$row['id']}' class='text-decoration-none'>
                            <div class='card'>
                                <img src='{$row['poster']}' class='card-img-top' alt='{$row['title']}'>
                                <div class='card-body text-center'>
                                    <h6 class='text-light'>{$row['title']} ({$row['year']})</h6>
                                </div>
                            </div>
                        </a>
                    </div>";
            }
            echo "</div>";
        }
    }

    $conn->close();
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
