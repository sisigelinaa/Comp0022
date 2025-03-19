<?php
session_start();
$loggedInUser = $_GET['username'] ?? $_SESSION['username'] ?? null;

if (isset($_GET['back'])) {
    if ($loggedInUser) {
        header("Location: Task 6 - Accounts/accountPage.php?username=$loggedInUser");
        exit();
    } else {
        header("Location: Task 1 - Dashboard/dashboard.php");
        exit();
    }
}
?>

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

    <div class="container mt-3">
        <a href="?back=true&username=<?php echo $loggedInUser; ?>" class="btn btn-light mb-3">← Back to Search</a>

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
                $formattedBoxOffice = $boxOffice ? "$" . number_format((int) $boxOffice, 0, '.', ',') : "N/A";

                $sqlGenres = "
                    SELECT g.genreName 
                    FROM movies_genres mg
                    JOIN genres g ON mg.genreId = g.genreId
                    WHERE mg.movieId = '$movieId'
                ";
                $resultGenres = $conn->query($sqlGenres);
                $genres = [];
                while ($row = $resultGenres->fetch_assoc()) {
                    $genres[] = trim($row['genreName']);
                }
                $genresString = implode(', ', $genres);

                $sqlActors = "
                    SELECT a.actorName 
                    FROM actorsMovies am
                    JOIN actors a ON am.actorId = a.actorId
                    WHERE am.movieId = '$movieId'
                ";
                $resultActors = $conn->query($sqlActors);
                $actors = [];
                while ($row = $resultActors->fetch_assoc()) {
                    $actors[] = trim($row['actorName']);
                }
                $actorsString = implode(', ', $actors);

                $sqlDirectors = "
                    SELECT d.directorName 
                    FROM directorsMovies dm
                    JOIN directors d ON dm.directorId = d.directorId
                    WHERE dm.movieId = '$movieId'
                ";
                $resultDirectors = $conn->query($sqlDirectors);
                $directors = [];
                while ($row = $resultDirectors->fetch_assoc()) {
                    $directors[] = trim($row['directorName']);
                }
                $directorsString = implode(', ', $directors);

                $sqlLanguages = "
                    SELECT l.languageName 
                    FROM movies_languages ml
                    JOIN languages l ON ml.languageId = l.languageId
                    WHERE ml.movieId = '$movieId'
                ";
                $resultLanguages = $conn->query($sqlLanguages);
                $languages = [];
                while ($row = $resultLanguages->fetch_assoc()) {
                    $languages[] = trim($row['languageName']);
                }
                $languagesString = implode(', ', $languages);

                // Part of TASK 3: Get users who disliked this movie
                $sqlDislikedUsers = "
                    SELECT userId 
                    FROM ratings 
                    WHERE movieId = '$movieId' AND rating <= 2
                ";
                $resultDislikedUsers = $conn->query($sqlDislikedUsers);

                if ($resultDislikedUsers->num_rows > 0) {
                    // Make into list
                    $dislikedUsers = [];
                    while ($row = $resultDislikedUsers->fetch_assoc()) {
                        $dislikedUsers[] = $row['userId'];
                    }

                    // LIKE conditions for genres WHERE clause - IN doesn't work well with strings
                    $likeConditions = [];
                    foreach ($genres as $genre) {
                        $likeConditions[] = "g.genreName LIKE '%" . $genre . "%'";
                    }
                    $genreLikeClause = implode(" OR ", $likeConditions);

                    // for each user, want sameGenreDislikes / sameGenresRated and otherGenreDislikes / otherGenresRated
                    // check for each user which prop is bigger, add to relevant count
                    $sameGenreDislikeCount = 0;
                    $otherGenreDislikeCount = 0;
                    foreach ($dislikedUsers as $userId) {

                        // Count how many times this user disliked other movies in any of the same genres
                        $sqlSameGenreDislikes = "
                            SELECT COUNT(*) AS sameGenreDislikes
                            FROM ratings r
                            JOIN movies_genres mg ON r.movieId = mg.movieId
                            JOIN genres g ON mg.genreId = g.genreId
                            WHERE r.userId = '$userId'
                            AND (" . $genreLikeClause . ")
                            AND r.rating <= 2
                            AND r.movieId != '$movieId'
                        ";
                        $resultSameGenreDislikes = $conn->query($sqlSameGenreDislikes);
                        $sameGenreDislikes = $resultSameGenreDislikes->fetch_assoc()['sameGenreDislikes'];

                        // Count how many times this user disliked other movies in different genres
                        $sqlOtherGenreDislikes = "
                            SELECT COUNT(*) AS otherGenreDislikes
                            FROM ratings r
                            JOIN movies_genres mg ON r.movieId = mg.movieId
                            JOIN genres g ON mg.genreId = g.genreId
                            WHERE r.userId = '$userId'
                            AND NOT (" . $genreLikeClause . ")
                            AND r.rating <= 2
                        ";
                        $resultOtherGenreDislikes = $conn->query($sqlOtherGenreDislikes);
                        $otherGenreDislikes = $resultOtherGenreDislikes->fetch_assoc()['otherGenreDislikes'];

                        // Count how many same genres were rated badly by this user
                        $sqlsameGenresRated = "
                            SELECT COUNT(DISTINCT g.genreName) AS genreCount
                            FROM ratings r
                            JOIN movies_genres mg ON r.movieId = mg.movieId
                            JOIN genres g ON mg.genreId = g.genreId
                            WHERE r.userId = '$userId'
                            AND (" . $genreLikeClause . ")
                            AND r.rating <= 2
                        ";
                        $resultsameGenresRated = $conn->query($sqlsameGenresRated);
                        $sameGenresRated = 0;
                        if ($resultsameGenresRated->num_rows > 0) {
                            $row = $resultsameGenresRated->fetch_assoc();
                            $sameGenresRated = $row['genreCount'];
                        }

                        // Count how many other genres were rated badly by this user
                        $sqlotherGenresRated = "
                            SELECT COUNT(DISTINCT g.genreName) AS genreCount
                            FROM ratings r
                            JOIN movies_genres mg ON r.movieId = mg.movieId
                            JOIN genres g ON mg.genreId = g.genreId
                            WHERE r.userId = '$userId'
                            AND NOT (" . $genreLikeClause . ")
                            AND r.rating <= 2
                        ";
                        $resultotherGenresRated = $conn->query($sqlotherGenresRated);
                        $otherGenresRated = 0;
                        if ($resultotherGenresRated->num_rows > 0) {
                            $row = $resultotherGenresRated->fetch_assoc();
                            $otherGenresRated = $row['genreCount'];
                        }

                        // Get the proportion of dislikes for same and other genres, add to relevant count
                        if ($sameGenresRated > 0 && $otherGenresRated > 0) {
                            $sameGenreDislikeProp = $sameGenreDislikes / $sameGenresRated;
                            $otherGenreDislikeProp = $otherGenreDislikes / $otherGenresRated;
                            if ($sameGenreDislikeProp > $otherGenreDislikeProp) {
                                $sameGenreDislikeCount++;
                            } else {
                                $otherGenreDislikeCount++;
                            }
                        }
                    }
                    // Hence find out if users disliked this movie because of its genre
                    $isGenreSpecificDislike = ($sameGenreDislikeCount > $otherGenreDislikeCount);
                } else {
                    $isGenreSpecificDislike = false; // No users disliked the movie
                }

                // Display movie details
                echo "<div class='card mx-auto' style='max-width: 400px; margin-bottom:20px;'>
                        <img src='{$movie['posterUrl']}' class='card-img-top' style='max-height: 200px; max-width: 150px;' alt='{$movie['title']}'>
                        <div class='card-body'>
                            <h2 class='card-title'>{$movie['title']}</h2>
                            <p class='card-text'><strong>IMDB Rating:</strong> ⭐ {$movie['imdbRating']}</p>
                            <p class='card-text'><strong>Genre:</strong> $genresString</p>
                            <p class='card-text'><strong>Director:</strong> $directorsString</p>
                            <p class='card-text'><strong>Actors:</strong> $actorsString</p>
                            <p class='card-text'><strong>Box Office: 💵</strong> $formattedBoxOffice</p>
                            <p class='card-text'><strong>Runtime:</strong> {$movie['runtime']}</p>
                            <p class='card-text'><strong>Language:</strong> $languagesString</p>
                            <p class='card-text'><strong>Predicted that users disliked this movie because of its genre:</strong> " . ($isGenreSpecificDislike ? 'True' : 'False') . "</p>
                        </div>
                    </div>";

                // Query to find top 5 movies liked by users who liked this movie
                $sqlTopMovies = "
                    SELECT m.movieId, m.title, AVG(r.rating) AS avg_rating
                    FROM ratings r
                    JOIN movies m ON r.movieId = m.movieId
                    WHERE r.userId IN (
                        SELECT DISTINCT userId
                        FROM ratings
                        WHERE movieId = '$movieId' AND rating > 4
                    )
                    AND r.movieId != '$movieId' -- Exclude the current movie
                    GROUP BY m.movieId
                    ORDER BY avg_rating DESC
                    LIMIT 5
                ";
                $resultTopMovies = $conn->query($sqlTopMovies);
                $topMovies = [];
                if ($resultTopMovies->num_rows > 0) {
                    while ($row = $resultTopMovies->fetch_assoc()) {
                        $topMovies[] = $row;
                    }
                }

                // Display the "Users who liked this movie also liked" section
                if (!empty($topMovies)) {
                    echo "<div class='card mx-auto mt-4' style='max-width: 400px;'>
                            <div class='card-body'>
                                <h3 class='card-title'>Users who liked this movie also liked:</h3>
                                <ul class='list-group list-group-flush'>";
                    foreach ($topMovies as $movie) {
                        echo "<li class='list-group-item'>
                                <a href='?movieId={$movie['movieId']}&username=$loggedInUser' class='text-decoration-none text-dark'>
                                    {$movie['title']}
                                </a>
                              </li>";
                    }
                    echo "</ul></div></div>";
                } else {
                    echo "<div class='alert alert-info text-center mt-4'>No additional recommendations found.</div>";
                }
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