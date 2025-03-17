<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: logIn.php");
    exit();
}

$servername = "db";
$username = "user";
$password = "password";
$database = "movielens";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
}

$loggedInUser = $_SESSION['username'];

// Create list
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_list'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $conn->query("INSERT INTO lists (username, title, description) VALUES ('$loggedInUser', '$title', '$description')");
}

// Edit/Update list
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_list'])) {
    $list_id = intval($_POST['list_id']);
    $new_title = $conn->real_escape_string($_POST['new_title']);
    $new_description = $conn->real_escape_string($_POST['new_description']);
    $conn->query("UPDATE lists SET title='$new_title', description='$new_description' WHERE id='$list_id'");
}

// Delete a movie from a list
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_movie'])) {
    $list_id = intval($_POST['list_id']);
    $movie_id = intval($_POST['movie_id']);
    $conn->query("DELETE FROM list_movies WHERE list_id='$list_id' AND movie_id='$movie_id'");
}

// Add movie
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_film'])) {
    $list_id = intval($_POST['list_id']);
    $movie_id = intval($_POST['movie_id']);
    $conn->query("INSERT INTO list_movies (list_id, movie_id) VALUES ('$list_id', '$movie_id')");
}

// Delete list
if (isset($_GET['delete_list'])) {
    $list_id = intval($_GET['delete_list']);
    $conn->query("DELETE FROM lists WHERE id = '$list_id'");
    $conn->query("DELETE FROM list_movies WHERE list_id = '$list_id'");
    header("Location: ../Task 6 - Accounts/accountPage.php");
    exit();
}

$lists = $conn->query("SELECT * FROM lists WHERE username = '$loggedInUser'");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Festival Movie Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
</head>

<body class="text-light" style="background-color: #141414;">
    <div class="container mt-4">
        <div class="text-end">
            <span class="text-light me-2 d-flex align-items-center">
                <i class="bi bi-person-circle me-2" style="font-size: 1.5rem;"></i>
                Welcome, <strong class="ms-1"><?= htmlspecialchars($loggedInUser) ?></strong>!
            </span>
            <a href="logOut.php" class="btn btn-primary">Log Out</a>
        </div>
        <h1 class="text-center">🎬 Movie Lists</h1>
        <div class="container mt-5">
            <h3 class="text-light">Create a New List</h3>
            <form method="POST" class="mb-4">
                <div class="mb-3">
                    <label class="text-light">Title</label>
                    <input type="text" name="title" class="form-control bg-dark text-light" placeholder="List Title"
                        required>
                </div>
                <div class="mb-3">
                    <label class="text-light">Description</label>
                    <textarea name="description" class="form-control bg-dark text-light"
                        placeholder="Description"></textarea>
                </div>
                <button type="submit" name="create_list" class="btn btn-success"><i class="fas fa-plus"></i> Create
                    List</button>
            </form>

            <h3 class="mt-4 text-light">My Movie Lists</h3>
            <div class="list-container">
                <?php while ($list = $lists->fetch_assoc()): ?>
                    <div class="card bg-secondary text-light mb-3 list-card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($list['title']) ?></h5>
                            <p class="card-text">📝 <?= htmlspecialchars($list['description']) ?></p>
                            <button class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#listModal<?= $list['id'] ?>">View</button>
                            <a href="?delete_list=<?= $list['id'] ?>" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to delete this list?');">Delete</a>
                        </div>
                    </div>

                    <div class="modal fade" id="listModal<?= $list['id'] ?>" data-bs-backdrop="static" tabindex="-1"
                        aria-labelledby="listModalLabel<?= $list['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content bg-dark text-light">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="listModalLabel<?= $list['id'] ?>">
                                        <?= htmlspecialchars($list['title']) ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>📝 <?= htmlspecialchars($list['description']) ?></p>

                                    <button class="btn btn-secondary mb-3" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#editForm<?= $list['id'] ?>" aria-expanded="false"
                                        aria-controls="editForm<?= $list['id'] ?>">
                                        Edit List
                                    </button>
                                    <div class="collapse mb-3" id="editForm<?= $list['id'] ?>">
                                        <form method="POST">
                                            <input type="hidden" name="list_id" value="<?= $list['id'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Title</label>
                                                <input type="text" name="new_title" class="form-control"
                                                    value="<?= htmlspecialchars($list['title']) ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="new_description"
                                                    class="form-control"><?= htmlspecialchars($list['description']) ?></textarea>
                                            </div>
                                            <button type="submit" name="update_list" class="btn btn-primary">Save
                                                Changes</button>
                                        </form>
                                    </div>

                                    <?php
                                    $list_id = $list['id'];
                                    $moviesQuery = $conn->query("SELECT m.movieId, m.title, m.posterUrl, m.genres 
                                    FROM movies m
                                    INNER JOIN list_movies lm ON m.movieId = lm.movie_id
                                    WHERE lm.list_id = '$list_id'
                                    ORDER BY m.genres ASC");

                                    $moviesByGenre = [];
                                    while ($movie = $moviesQuery->fetch_assoc()) {
                                        $genres = explode('|', $movie['genres']);
                                        foreach ($genres as $genre) {
                                            $genre = trim($genre);
                                            if (!isset($moviesByGenre[$genre])) {
                                                $moviesByGenre[$genre] = [];
                                            }
                                            $moviesByGenre[$genre][] = $movie;
                                        }
                                    }
                                    ksort($moviesByGenre);
                                    ?>

                                    <?php foreach ($moviesByGenre as $genre => $movies): ?>
                                        <h6 class="mt-3"><?= htmlspecialchars($genre) ?></h6>
                                        <div class="row">
                                            <?php foreach ($movies as $movie): ?>
                                                <div class="col-4 text-center">
                                                    <a href="../movieData.php?id=<?= $movie['movieId'] ?>"
                                                        class="text-decoration-none">
                                                        <img src="<?= $movie['posterUrl'] ?>"
                                                            alt="<?= htmlspecialchars($movie['title']) ?>"
                                                            class="img-fluid rounded">
                                                        <p class="text-light small"><?= htmlspecialchars($movie['title']) ?></p>
                                                    </a>
                                                    <form method="POST" class="mt-1">
                                                        <input type="hidden" name="list_id" value="<?= $list['id'] ?>">
                                                        <input type="hidden" name="movie_id" value="<?= $movie['movieId'] ?>">
                                                        <button type="submit" name="delete_movie" class="btn btn-sm btn-danger"> <i
                                                                class="bi bi-x"></i></button>
                                                    </form>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
</body>

<h2 class="text-center mt-4"> Search </h2>
<div class="text-center my-3 genre-bar">
    <?php
    $genres = ["Action", "Comedy", "Drama", "Horror", "Sci-Fi", "Romance", "Thriller", "Crime", "Mystery", "Musical"];
    foreach ($genres as $genre) {
        echo "<a href='?genre=$genre' class='btn btn-danger'>$genre</a>";
    }
    ?>
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
    </div>
    <div class="text-center mt-3">
        <button type="submit" class="btn btn-danger">Search</button>
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

        echo "
                <div class='col'>
                 <a href='../movieData.php?movieId=$movieId' class='text-decoration-none'>
                    <div class='card bg-dark movie-card h-100 d-flex flex-column'>
                        <img src='$poster' class='card-img-top' alt='$title'>
                        <div class='card-body text-center d-flex flex-column flex-grow-1'>
                            <h6 class='text-light'>$title</h6>
                            <p class='text-light'>⭐ $rating</p>
                            <p class='text-light'>$genre</p>
                            <p class='text-light'><strong>Actors:</strong> $actors</p>
                            <div class = 'mt-auto'>
                            <div class='dropdown'>
                                <button class='btn btn-primary dropdown-toggle' type='button' id='dropdownMenuButton_$movieId' data-bs-toggle='dropdown' aria-expanded='false'>
                                    <i class='bi bi-plus'></i>
                                </button>
                                <ul class='dropdown-menu' aria-labelledby='dropdownMenuButton_$movieId'>";

        $listsResult = $conn->query("SELECT * FROM lists WHERE username = '$loggedInUser'");
        if ($listsResult->num_rows > 0) {
            while ($list = $listsResult->fetch_assoc()) {
                $listId = htmlspecialchars($list['id']);
                $listTitle = htmlspecialchars($list['title']);
                echo "
                                        <li>
                                            <form method='POST' class='dropdown-item p-0'>
                                                <input type='hidden' name='movie_id' value='$movieId'>
                                                <input type='hidden' name='list_id' value='$listId'>
                                                <button type='submit' name='add_film' class='btn btn-link text-decoration-none text-reset w-100'>$listTitle</button>
                                            </form>
                                        </li>";
            }
        } else {
            echo "<li class='dropdown-item'>No lists available</li>";
        }

        echo "              </ul>
                            </div>
                            </div>
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
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>