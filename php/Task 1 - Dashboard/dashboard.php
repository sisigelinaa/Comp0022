<?php
$servername = "db";
$username = "user";
$password = "password";
$database = "movielens";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Movie Info Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../styles.css">
</head>

<body class="text-light" style="background-color: #141414;">
    <div class="container mt-4">
        <div class="container mt-4">
            <div class="text-end">
                <a href="../Task 6 - Accounts/logIn.php" class="btn btn-primary">Log In</a>
            </div>
            <h1 class="text-center">🎬 Movie Finder</h1>

            <div class="text-center my-3 genre-bar">
                <?php
                $genres = ["Action", "Comedy", "Drama", "Horror", "Sci-Fi", "Romance", "Thriller", "Crime", "Mystery", "Musical"];
                foreach ($genres as $genre) {
                    echo "<a href='?genre=$genre' class='btn btn-danger'>$genre</a>";
                }
                ?>
            </div>

            <div class="text-center my-4">
                <a href="../Task 3 - Analysis/audiencePatterns.php" class="btn btn-primary btn-sm">View Genre Correlations</a>
                <a href="../Task 2 - Reports/genreReport.php" class="btn btn-info btn-sm">View Genre Reports (Table)</a>
                <a href="../Task 2 - Reports/genreHistograms.php?type=popularity" class="btn btn-primary btn-sm">View Genre Popularity
                    Histogram</a>
                <a href="../Task 2 - Reports/genreHistograms.php?type=polarization" class="btn btn-warning btn-sm">View Genre Polarization
                    Histogram</a>
                <a href="../Task 4 - Prediction/predict_rating.php" class="btn btn-info btn-sm px-3" style="background-color: #17a2b8; border-color: #17a2b8;">⭐ Predict Movie Rating</a>
            </div>

            <form method="GET" class="my-4">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="movie" class="form-control" placeholder="Search for a movie...">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="actor" class="form-control" placeholder="Search by actor...">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="year_from" class="form-control" placeholder="Year From">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="year_to" class="form-control" placeholder="Year To">
                    </div>
                    <div class="col-md-2">
                        <select name="rating" class="form-control">
                            <option value="">Min Rating</option>
                            <option value="9">9+</option>
                            <option value="8">8+</option>
                            <option value="7">7+</option>
                            <option value="6">6+</option>
                            <option value="5">5+</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i>
                            Search</button>
                    </div>
                </div>
            </form>


            <?php
            $sql = "SELECT * FROM movies WHERE 1=1";

            if (!empty($_GET['movie'])) {
                $movie = $conn->real_escape_string($_GET['movie']);
                $sql .= " AND title LIKE '%$movie%'";
            }

            if (!empty($_GET['genre'])) {
                $genre = $conn->real_escape_string($_GET['genre']);
                $sql .= " AND genres LIKE '%$genre%'";
            }

            if (!empty($_GET['actor'])) {
                $actor = $conn->real_escape_string($_GET['actor']);
                $sql .= " AND actors LIKE '%$actor%'";
            }

            if (!empty($_GET['year_from'])) {
                $yearFrom = intval($_GET['year_from']);
                $sql .= " AND year >= $yearFrom";
            }

            if (!empty($_GET['year_to'])) {
                $yearTo = intval($_GET['year_to']);
                $sql .= " AND year <= $yearTo";
            }

            if (!empty($_GET['rating'])) {
                $rating = floatval($_GET['rating']);
                $sql .= " AND imdbRating >= $rating";
            }

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo "<div class='row row-cols-2 row-cols-md-3 row-cols-lg-6 g-2'>";
                while ($row = $result->fetch_assoc()) {
                    $title = htmlspecialchars($row['title']);
                    $year = htmlspecialchars($row['year']);
                    $poster = !empty($row['posterUrl']) ? $row['posterUrl'] : "no-image.png";
                    $rating = htmlspecialchars($row['imdbRating']);
                    $genre = htmlspecialchars($row['genres']);
                    $actors = htmlspecialchars($row['actors']);
                    $movieId = $row['movieId'];

                    echo "<div class='col'>
                    <a href='../movieData.php?movieId=$movieId' class='text-decoration-none'>
                        <div class='card bg-dark movie-card h-100'>
                            <img src='$poster' class='card-img-top' alt='$title'>
                            <div class='card-body text-center'>
                                <h6 class='text-light'>$title</h6>
                                <p class='text-light'>⭐ $rating</p>
                                <p class='text-light'>$genre</p>
                                <p class='text-light'><strong>Actors:</strong> $actors</p>
                            </div>
                        </div>
                    </a>
                </div>";
                }
                echo "</div>";
            } else {
                echo "<div class='alert alert-warning text-center'>No movies found.</div>";
            }
            ?>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>