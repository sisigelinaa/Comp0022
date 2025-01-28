<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Searcher</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Welcome to Movie Searcher</h1>
        <p>Find information about your favorite movies with ease!</p>
    </header>
    <main>
        <section class="search-section">
            <h2>Search for a Movie</h2>
            <form method="GET" action="index.php">
                <input type="text" name="query" id="search-input" placeholder="Enter movie title..." required>
                <button type="submit" id="search-button">Search</button>
            </form>
        </section>
        <section class="results-section">
            <h2>Search Results</h2>
            <div id="results-container">
                <?php
                if (isset($_GET['query']) && !empty($_GET['query'])) {
                    $query = htmlspecialchars($_GET['query']); // Sanitize user input
                    $conn = new mysqli("db", "root", "123", "movielens");

                    // Check for connection errors
                    if ($conn->connect_error) {
                        echo "<p>Database connection failed: " . $conn->connect_error . "</p>";
                    } else {
                        $sql = "SELECT title, genres FROM movies WHERE title LIKE ?";
                        $stmt = $conn->prepare($sql);
                        $searchTerm = "%" . $query . "%";
                        $stmt->bind_param("s", $searchTerm);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<div class='movie-item'>";
                                echo "<h3>" . $row['title'] . "</h3>";
                                echo "<p>Genres: " . $row['genres'] . "</p>";
                                echo "</div>";
                            }
                        } else {
                            echo "<p>No movies found.</p>";
                        }

                        $stmt->close();
                    }

                    $conn->close();
                }
                ?>
            </div>
        </section>
    </main>
    <footer>
    </footer>
</body>
</html>
