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

                // part of TASK 3
                // get users who disliked this movie
                $sqlDislikedUsers = "
                SELECT userId 
                FROM ratings 
                WHERE movieId = '$movieId' AND rating <= 2
                ";
                $resultDislikedUsers = $conn->query($sqlDislikedUsers);

                if ($resultDislikedUsers->num_rows > 0) {
                    // make into list
                    $dislikedUsers = [];
                    while ($row = $resultDislikedUsers->fetch_assoc()) {
                        $dislikedUsers[] = $row['userId'];
                    }

                    // get the genres of current movie
                    $sqlMovieGenres = "
                        SELECT g.genreName 
                        FROM movies_genres mg
                        JOIN genres g ON mg.genreId = g.genreId
                        WHERE mg.movieId = '$movieId'
                    ";
                    $resultMovieGenres = $conn->query($sqlMovieGenres);
                    $movieGenres = [];
                    while ($row = $resultMovieGenres->fetch_assoc()) {
                        $movieGenres[] = $row['genreName'];
                    }

                    // count how many times these users also disliked other movies in any of the same genres
                    $sqlSameGenreDislikes = "
                        SELECT COUNT(*) AS sameGenreDislikes
                        FROM ratings r
                        JOIN movies_genres mg ON r.movieId = mg.movieId
                        JOIN genres g ON mg.genreId = g.genreId
                        WHERE r.userId IN (" . implode(',', $dislikedUsers) . ")
                        AND g.genreName IN ('" . implode("','", $movieGenres) . "')
                        AND r.rating <= 2
                        AND r.movieId != '$movieId'
                    ";
                    $resultSameGenreDislikes = $conn->query($sqlSameGenreDislikes);
                    $sameGenreDislikes = $resultSameGenreDislikes->fetch_assoc()['sameGenreDislikes'];

                    // count how many times these users disliked other movies in different genres
                    $sqlOtherGenreDislikes = "
                        SELECT COUNT(*) AS otherGenreDislikes
                        FROM ratings r
                        JOIN movies_genres mg ON r.movieId = mg.movieId
                        JOIN genres g ON mg.genreId = g.genreId
                        WHERE r.userId IN (" . implode(',', $dislikedUsers) . ")
                        AND g.genreName NOT IN ('" . implode("','", $movieGenres) . "')
                        AND r.rating <= 2
                    ";
                    $resultOtherGenreDislikes = $conn->query($sqlOtherGenreDislikes);
                    $otherGenreDislikes = $resultOtherGenreDislikes->fetch_assoc()['otherGenreDislikes'];

                    // find out if users disliked this movie because of its genre using counts
                    $isGenreSpecificDislike = ($sameGenreDislikes > $otherGenreDislikes);
                } else {
                    $isGenreSpecificDislike = false; // no users disliked the movie
                }

                echo "<div class='card mx-auto' style='max-width: 400px; margin-bottom:20px;'>
            <img src='{$movie['posterUrl']}' class='card-img-top' style ='max-height: 200px; max-width: 150px;' alt='{$movie['title']}'>
            <div class='card-body'>
                <h2 class='card-title'>{$movie['title']}</h2>
                <p class='card-text'><strong>IMDB Rating:</strong> ⭐ {$movie['imdbRating']}</p>
                <p class='card-text'><strong>Genre:</strong> {$movie['genres']}</p>
                <p class='card-text'><strong>Director:</strong> {$movie['directors']}</p>
                <p class='card-text'><strong>Actors:</strong> {$movie['actors']}</p>
                <p class='card-text'><strong>Box Office: 💵</strong> {$formattedBoxOffice}</p>
                <p class='card-text'><strong>Runtime:</strong> {$movie['runtime']}</p>
                <p class='card-text'><strong>Language:</strong> {$movie['language']}</p>
                <p class='card-text'><strong>Predicted that users disliked this movie because of its genre:</strong> " . ($isGenreSpecificDislike ? 'True' : 'False') . "</p>
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