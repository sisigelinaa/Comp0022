<?php
$servername = "db";
$username = "user";
$password = "password";
$database = "movielens";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
}

// Fetch genres and their corresponding IMDb ratings
$sql = "
    SELECT g.genreName, m.imdbRating 
    FROM movies m
    JOIN movies_genres mg ON m.movieId = mg.movieId
    JOIN genres g ON mg.genreId = g.genreId
    WHERE m.imdbRating IS NOT NULL AND g.genreName <> '(no genres listed)'
";
$result = $conn->query($sql);

$genreData = [];
while ($row = $result->fetch_assoc()) {
    $genre = $row['genreName'];
    $rating = floatval($row['imdbRating']);

    if (!isset($genreData[$genre])) {
        $genreData[$genre] = [];
    }
    $genreData[$genre][] = $rating;
}

// Compute statistics for each genre
$report = [];
foreach ($genreData as $genre => $ratings) {
    $count = count($ratings);
    $avg = array_sum($ratings) / $count;
    $stddev = sqrt(array_sum(array_map(fn($r) => pow($r - $avg, 2), $ratings)) / $count);

    $report[] = [
        'genre' => $genre,
        'count' => $count,
        'average' => round($avg, 2),
        'stddev' => round($stddev, 2)
    ];
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genre Reports (Table)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sort-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 14px;
        }
        .sort-btn.active {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
    <script>
        function sortTable(tableId, columnIndex, order) {
            const table = document.getElementById(tableId);
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));

            rows.sort((rowA, rowB) => {
                let a = rowA.cells[columnIndex].innerText.trim();
                let b = rowB.cells[columnIndex].innerText.trim();

                a = isNaN(a) ? a.toLowerCase() : parseFloat(a);
                b = isNaN(b) ? b.toLowerCase() : parseFloat(b);

                return order === "asc" ? (a > b ? 1 : -1) : (a < b ? 1 : -1);
            });

            tbody.innerHTML = "";
            rows.forEach(row => tbody.appendChild(row));

            // Update button styles
            document.querySelectorAll(".sort-btn").forEach(btn => btn.classList.remove("active"));
            document.getElementById(`${tableId}-col${columnIndex}-${order}`).classList.add("active");
        }

        // Initial sort: "Average Rating" & "Rating Std. Dev." both descending
        window.onload = function () {
            sortTable("popularityTable", 2, "desc");
            sortTable("polarizationTable", 1, "desc");
        };
    </script>
</head>

<body class="bg-dark text-light">
    <div class="container mt-4">
        <a href="../Task 1 - Dashboard/dashboard.php" class="btn btn-light mb-3">← Back to Search</a>
        <h1 class="text-center mb-4">Genre Reports (Table)</h1>

        <!-- Popularity Report Table -->
        <h2>Popularity Report</h2>
        <p class="text-secondary">This table shows the number of movies available for each genre along with their average IMDb rating. A higher count indicates a more popular genre among viewers.</p>
        <table id="popularityTable" class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>
                        Genre
                        <button class="sort-btn" id="popularityTable-col0-asc" onclick="sortTable('popularityTable', 0, 'asc')">▲</button>
                        <button class="sort-btn" id="popularityTable-col0-desc" onclick="sortTable('popularityTable', 0, 'desc')">▼</button>
                    </th>
                    <th>
                        Movie Count
                        <button class="sort-btn" id="popularityTable-col1-asc" onclick="sortTable('popularityTable', 1, 'asc')">▲</button>
                        <button class="sort-btn" id="popularityTable-col1-desc" onclick="sortTable('popularityTable', 1, 'desc')">▼</button>
                    </th>
                    <th>
                        Average Rating
                        <button class="sort-btn active" id="popularityTable-col2-asc" onclick="sortTable('popularityTable', 2, 'asc')">▲</button>
                        <button class="sort-btn" id="popularityTable-col2-desc" onclick="sortTable('popularityTable', 2, 'desc')">▼</button>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['genre']); ?></td>
                        <td><?php echo $row['count']; ?></td>
                        <td><?php echo $row['average']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Polarization Report Table -->
        <h2 class="mt-5">Polarization Report</h2>
        <p class="text-secondary">This table measures how much opinions on each genre vary. A higher standard deviation means ratings for that genre are more spread out (more polarizing), while a lower one suggests general agreement among viewers.</p>
        <table id="polarizationTable" class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>
                        Genre
                        <button class="sort-btn" id="polarizationTable-col0-asc" onclick="sortTable('polarizationTable', 0, 'asc')">▲</button>
                        <button class="sort-btn" id="polarizationTable-col0-desc" onclick="sortTable('polarizationTable', 0, 'desc')">▼</button>
                    </th>
                    <th>
                        Rating Standard Deviation
                        <button class="sort-btn active" id="polarizationTable-col1-asc" onclick="sortTable('polarizationTable', 1, 'asc')">▲</button>
                        <button class="sort-btn" id="polarizationTable-col1-desc" onclick="sortTable('polarizationTable', 1, 'desc')">▼</button>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['genre']); ?></td>
                        <td><?php echo $row['stddev']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
