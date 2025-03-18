<?php
$servername = "db";
$username = "user";
$password = "password";
$database = "movielens";

session_start();

$mysqli = new mysqli($servername, $username, $password, $database);
if ($mysqli->connect_error) {
    die("<div class='alert alert-danger'>Connection failed: " . $mysqli->connect_error . "</div>");
}

$openness = $agreeableness = $emotionalStability = $conscientiousness = $extraversion = 0;
$correlations = [];
$selectedGenres = "All Genres";

function test_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Get all available genres from the database
$genreQuery = "SELECT genreId, genreName FROM genres ORDER BY genreName ASC";
$genreResult = $mysqli->query($genreQuery);
$allGenres = $genreResult->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if any genres were selected
    if (isset($_POST["genres"]) && is_array($_POST["genres"]) && count($_POST["genres"]) > 0) {
        // Sanitize each selected genre
        $expandedTags = array_map('test_input', $_POST["genres"]);
        $selectedGenres = implode(', ', $expandedTags);

        // LIKE conditions for genres WHERE clause - IN doesn't work well with strings
        $likeConditions = [];
        foreach ($expandedTags as $tag) {
            $likeConditions[] = "TRIM(g.genreName) LIKE '%" . $tag . "%'";
        }

        $whereClause = implode(" OR ", $likeConditions);

        $query = "
            SELECT 
                AVG(pd.openness) AS opennessAvg, 
                AVG(pd.agreeableness) AS agreeablenessAvg, 
                AVG(pd.emotionalStability) AS emotionalStabilityAvg, 
                AVG(pd.conscientiousness) AS conscientiousnessAvg, 
                AVG(pd.extraversion) AS extraversionAvg
            FROM personalityData pd
            WHERE pd.userId IN (
                SELECT DISTINCT rp.userId
                FROM ratingsPersonality rp
                INNER JOIN movies_genres mg ON rp.movieId = mg.movieId
                INNER JOIN genres g ON mg.genreId = g.genreId
                WHERE " . $whereClause . "
                GROUP BY rp.userId
                HAVING AVG(rp.rating) > 4
            )
        ";

        $result = $mysqli->query($query);
        if (!$result) {
            die("<div class='alert alert-danger'>SQL Error: " . $mysqli->error . "</div>");
        }
        $predicted_traits = $result->fetch_assoc();

        $openness = $predicted_traits["opennessAvg"] ?? 0;
        $agreeableness = $predicted_traits["agreeablenessAvg"] ?? 0;
        $emotionalStability = $predicted_traits["emotionalStabilityAvg"] ?? 0;
        $conscientiousness = $predicted_traits["conscientiousnessAvg"] ?? 0;
        $extraversion = $predicted_traits["extraversionAvg"] ?? 0;
    }
}

// Correlation genre-personality
$correlationQuery = "
    SELECT g.genreName, 
           AVG(pd.openness) AS openness, 
           AVG(pd.agreeableness) AS agreeableness, 
           AVG(pd.emotionalStability) AS emotionalStability, 
           AVG(pd.conscientiousness) AS conscientiousness, 
           AVG(pd.extraversion) AS extraversion
    FROM personalityData pd
    JOIN ratingsPersonality rp ON pd.userId = rp.userId
    JOIN movies_genres mg ON rp.movieId = mg.movieId
    JOIN genres g ON mg.genreId = g.genreId
    GROUP BY g.genreName
    ORDER BY g.genreName ASC;
";

$correlationResult = $mysqli->query($correlationQuery);
$correlations = $correlationResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Personality & Viewing Preferences</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .genre-checkbox {
            display: inline-block;
            margin: 5px;
            padding: 8px 15px;
            background-color: #343a40;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .genre-checkbox:hover {
            background-color: #495057;
        }
        .genre-checkbox input {
            margin-right: 5px;
        }
        .genre-checkboxes {
            max-width: 80%;
            margin: 0 auto;
            text-align: left;
        }
    </style>
    <script>
        function sortTable(tableId, columnIndex) {
            let table = document.getElementById(tableId);
            let rows = Array.from(table.rows).slice(1);
            let ascending = table.getAttribute("data-sort-asc") === "true";

            rows.sort((rowA, rowB) => {
                let cellA = rowA.cells[columnIndex].innerText.trim();
                let cellB = rowB.cells[columnIndex].innerText.trim();
                let numA = parseFloat(cellA);
                let numB = parseFloat(cellB);

                return ascending ? numA - numB : numB - numA;
            });

            table.tBodies[0].append(...rows);
            table.setAttribute("data-sort-asc", !ascending);
        }
        
        function selectAllGenres() {
            const checkboxes = document.querySelectorAll('input[name="genres[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
        }
        
        function deselectAllGenres() {
            const checkboxes = document.querySelectorAll('input[name="genres[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    </script>
</head>
<body class="bg-dark text-light">
    <div class="container mt-4">
        <a href="../Task 1 - Dashboard/dashboard.php" class="btn btn-light mb-3">&larr; Back to Dashboard</a>
        <h2 class="text-center">Personality Traits & Viewing Preferences</h2>

        <form method="POST" class="text-center mt-4">
            <h4>Select Genres</h4>
            <div class="mb-3">
                <button type="button" class="btn btn-sm btn-outline-light me-2" onclick="selectAllGenres()">Select All</button>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="deselectAllGenres()">Deselect All</button>
            </div>
            
            <div class="genre-checkboxes mb-3">
                <?php foreach ($allGenres as $genre): ?>
                <label class="genre-checkbox">
                    <input type="checkbox" name="genres[]" value="<?= htmlspecialchars($genre['genreName']) ?>">
                    <?= htmlspecialchars($genre['genreName']) ?>
                </label>
                <?php endforeach; ?>
            </div>
            
            <button type="submit" class="btn btn-primary mt-3">Analyze</button>
        </form>

        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["genres"])): ?>
        <h3 class="text-center mt-4">Analysis Results for <span class="text-warning"><?= htmlspecialchars($selectedGenres) ?></span></h3>
        
        <table id="traitsTable" class="table table-dark table-bordered mt-3" data-sort-asc="true">
            <thead>
                <tr>
                    <th onclick="sortTable('traitsTable', 0)">Personality Trait ⬍</th>
                    <th onclick="sortTable('traitsTable', 1)">Score ⬍</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Openness</td><td><?= round($openness, 2) ?></td></tr>
                <tr><td>Agreeableness</td><td><?= round($agreeableness, 2) ?></td></tr>
                <tr><td>Emotional Stability</td><td><?= round($emotionalStability, 2) ?></td></tr>
                <tr><td>Conscientiousness</td><td><?= round($conscientiousness, 2) ?></td></tr>
                <tr><td>Extraversion</td><td><?= round($extraversion, 2) ?></td></tr>
            </tbody>
        </table>

        <h3 class="text-center mt-4">Genre & Personality Trait Correlations</h3>
        <table id="correlationsTable" class="table table-dark table-bordered mt-3" data-sort-asc="true">
            <thead>
                <tr>
                    <th onclick="sortTable('correlationsTable', 0)">Genre ⬍</th>
                    <th onclick="sortTable('correlationsTable', 1)">Openness ⬍</th>
                    <th onclick="sortTable('correlationsTable', 2)">Agreeableness ⬍</th>
                    <th onclick="sortTable('correlationsTable', 3)">Emotional Stability ⬍</th>
                    <th onclick="sortTable('correlationsTable', 4)">Conscientiousness ⬍</th>
                    <th onclick="sortTable('correlationsTable', 5)">Extraversion ⬍</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($correlations as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['genreName']) ?></td>
                        <td><?= round($row['openness'], 2) ?></td>
                        <td><?= round($row['agreeableness'], 2) ?></td>
                        <td><?= round($row['emotionalStability'], 2) ?></td>
                        <td><?= round($row['conscientiousness'], 2) ?></td>
                        <td><?= round($row['extraversion'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</body>
</html>