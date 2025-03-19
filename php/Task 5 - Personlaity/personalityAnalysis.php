<?php
$servername = "db";
$username = "user";
$password = "password";
$database = "movielens";

$mysqli = new mysqli($servername, $username, $password, $database);
if ($mysqli->connect_error) {
    die("<div class='alert alert-danger'>Connection failed: " . $mysqli->connect_error . "</div>");
}

$openness = $agreeableness = $emotionalStability = $conscientiousness = $extraversion = 0;
$selectedGenres = "All Genres";

function test_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Get all available genres from the database
$genreQuery = "SELECT genreId, genreName FROM genres ORDER BY genreName ASC";
$genreResult = $mysqli->query($genreQuery);
$allGenres = $genreResult->fetch_all(MYSQLI_ASSOC);
// Mapping to get genre ID from genre name
$genreNameToId = [];
foreach ($allGenres as $genre) {
    $genreNameToId[trim($genre['genreName'])] = $genre['genreId'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if any genres were selected
    if (isset($_POST["genres"]) && is_array($_POST["genres"]) && count($_POST["genres"]) > 0) {
        // Sanitize each selected genre
        $expandedTags = array_map('test_input', $_POST["genres"]);
        $selectedGenres = implode(', ', $expandedTags);

        // Convert genre names to genre IDs
        $selectedGenreIds = [];
        foreach ($_POST["genres"] as $genreName) {
            if (isset($genreNameToId[trim($genreName)])) {
                $selectedGenreIds[] = $genreNameToId[trim($genreName)];
            }
        }

        $whereClause = "g.genreId IN (" . implode(",", $selectedGenreIds) . ")";    // genreId searching with IN is faster than LIKE with genreName

        // Get average personality trait scores for viewers who liked selected genres
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

// Histogram data
// Calculate average trait scores across all genres
$averageTraitQuery = "
    SELECT 
        AVG(pd.openness) AS avg_openness, 
        AVG(pd.agreeableness) AS avg_agreeableness, 
        AVG(pd.emotionalStability) AS avg_emotionalStability, 
        AVG(pd.conscientiousness) AS avg_conscientiousness, 
        AVG(pd.extraversion) AS avg_extraversion
    FROM personalityData pd
    JOIN ratingsPersonality rp ON pd.userId = rp.userId
    JOIN movies_genres mg ON rp.movieId = mg.movieId
    JOIN genres g ON mg.genreId = g.genreId;
";

$averageTraitResult = $mysqli->query($averageTraitQuery);
$averageTraits = $averageTraitResult->fetch_assoc();

// Extract average trait scores
$avgOpenness = $averageTraits['avg_openness'];
$avgAgreeableness = $averageTraits['avg_agreeableness'];
$avgEmotionalStability = $averageTraits['avg_emotionalStability'];
$avgConscientiousness = $averageTraits['avg_conscientiousness'];
$avgExtraversion = $averageTraits['avg_extraversion'];

// Personality Score Averages for each genre
$avgScoresQuery = "
    SELECT g.genreId, g.genreName, 
           AVG(pd.openness) AS openness, 
           AVG(pd.agreeableness) AS agreeableness, 
           AVG(pd.emotionalStability) AS emotionalStability, 
           AVG(pd.conscientiousness) AS conscientiousness, 
           AVG(pd.extraversion) AS extraversion
    FROM personalityData pd
    JOIN ratingsPersonality rp ON pd.userId = rp.userId
    JOIN movies_genres mg ON rp.movieId = mg.movieId
    JOIN genres g ON mg.genreId = g.genreId
    GROUP BY g.genreId
    ORDER BY g.genreName ASC;
";

$avgScoresResult = $mysqli->query($avgScoresQuery);
$relativeScoreChanges = $avgScoresResult->fetch_all(MYSQLI_ASSOC);

// Compute relative change for each genre
foreach ($relativeScoreChanges as &$row) {
    $row['openness'] = ($row['openness'] - $avgOpenness) / $avgOpenness;
    $row['agreeableness'] = ($row['agreeableness'] - $avgAgreeableness) / $avgAgreeableness;
    $row['emotionalStability'] = ($row['emotionalStability'] - $avgEmotionalStability) / $avgEmotionalStability;
    $row['conscientiousness'] = ($row['conscientiousness'] - $avgConscientiousness) / $avgConscientiousness;
    $row['extraversion'] = ($row['extraversion'] - $avgExtraversion) / $avgExtraversion;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Personality & Viewing Preferences</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .chart-container {
            width: 80%;
            height: 60vh;
            margin: 0 auto;
        }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="container mt-4">
        <a href="../Task 1 - Dashboard/dashboard.php" class="btn btn-light mb-3">&larr; Back to Dashboard</a>
        <h2 class="text-center">Personality Traits & Viewing Preferences</h2>
        <div style="height: 40px;"></div>

        <h3 class="text-center mt-4">Relative Change in Personality Trait Scores</h3>
        <h5 class="text-center mt-4">For selected genre. Relative changes are compared to relevant overall average across all genres</h5>
        
        <!-- Genre Dropdown Selection -->
        <div class="row mb-4">
            <div class="col-md-4 mx-auto">
                <select id="genreSelect" class="form-select bg-dark text-light">
                    <option value="">Select a Genre...</option>
                    <?php foreach ($allGenres as $genre): ?>
                        <option value="<?= $genre['genreId'] ?>"><?= htmlspecialchars($genre['genreName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <!-- Chart Container -->
        <div class="chart-container mt-4">
            <canvas id="personalityChart"></canvas>
        </div>

        <div style="height: 40px;"></div>

        <h3 class="text-center mt-4">Average Personality Trait Scores for specific genre combination</h3>
        <h6 class="text-center">Relevant tables are very big, please be patient...</h6>
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
            
            <button type="submit" id="analyzeButton" class="btn btn-primary mt-3">Analyze</button>
            <script>    // Display Lodaing... when the Analyze button is clicked
                // Add an event listener to the form
                document.querySelector('form').addEventListener('submit', function(event) {
                    const analyzeButton = document.getElementById('analyzeButton');

                    // Change text to "Loading..." and disable it
                    analyzeButton.innerHTML = 'Loading...';
                    analyzeButton.disabled = true;

                    setTimeout(function() {
                        analyzeButton.innerHTML = 'Analyze';
                        analyzeButton.disabled = false;
                    }, 15000);
                });
            </script>
            <div style="height: 40px;"></div>
        </form>

        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["genres"])): ?>
        <h3 class="text-center mt-4">Average Personality Trait Scores for Viewers who liked <span class="text-warning"><?= htmlspecialchars($selectedGenres) ?></span></h3>
        <h5 class="text-center">Personality Trait Scores Range from 1 - 7</h5>

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

        <div style="height: 40px;"></div>

        <?php endif; ?>
    </div>

    <script>
        // Define consistent colors for each personality trait
        const traitColors = {
            openness: 'rgba(54, 162, 235, 0.8)',
            agreeableness: 'rgba(75, 192, 192, 0.8)',
            emotionalStability: 'rgba(255, 99, 132, 0.8)',
            conscientiousness: 'rgba(255, 206, 86, 0.8)',
            extraversion: 'rgba(153, 102, 255, 0.8)',
        };

        // Prepare data for charts
        const relativeScoreChanges = <?= json_encode($relativeScoreChanges) ?>;
        let personalityChart;

        // Initialize the chart
        function initChart() {
            const ctx = document.getElementById('personalityChart').getContext('2d');
            
            personalityChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Openness', 'Agreeableness', 'Emotional Stability', 'Conscientiousness', 'Extraversion'],
                    datasets: [{
                        label: 'Relative Change',
                        data: [0, 0, 0, 0, 0], // Default empty data
                        backgroundColor: [
                            traitColors.openness,
                            traitColors.agreeableness,
                            traitColors.emotionalStability,
                            traitColors.conscientiousness,
                            traitColors.extraversion,
                        ],
                        borderWidth: 1,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)',
                            },
                            ticks: {
                                color: '#fff',
                            },
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)',
                            },
                            ticks: {
                                color: '#fff',
                            },
                        },
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Select a genre',
                            color: '#fff',
                            font: { size: 16 },
                        },
                        legend: {
                            display: false,
                        },
                    },
                },
            });
        }

        // Update chart based on selected genre
        function updateChart(genreId) {
            const selectedGenre = relativeScoreChanges.find(item => item.genreId === genreId);
            
            if (!selectedGenre) {
                console.error('Genre not found:', genreId);
                return;
            }
            
            personalityChart.data.datasets[0].data = [
                selectedGenre.openness,
                selectedGenre.agreeableness,
                selectedGenre.emotionalStability,
                selectedGenre.conscientiousness,
                selectedGenre.extraversion,
            ];
            
            personalityChart.options.plugins.title.text = selectedGenre.genreName;
            personalityChart.update();
        }

        // Genre selector handler
        document.getElementById('genreSelect').addEventListener('change', function() {
            const genreId = this.value;
            if (genreId) {
                updateChart(genreId);
            }
        });

        // Select/deselect all genres functions
        function selectAllGenres() {
            const checkboxes = document.querySelectorAll('input[name="genres[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = true);
        }

        function deselectAllGenres() {
            const checkboxes = document.querySelectorAll('input[name="genres[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = false);
        }

        // Table sorting function
        function sortTable(tableId, colIndex) {
            const table = document.getElementById(tableId);
            const isAscending = table.getAttribute('data-sort-asc') === 'true';
            const rows = Array.from(table.rows).slice(1); // Skip header row

            rows.sort((a, b) => {
                const cellA = a.cells[colIndex].textContent.trim();
                const cellB = b.cells[colIndex].textContent.trim();
                
                if (colIndex === 0) { // Sort by text
                    return isAscending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
                } else { // Sort by number
                    return isAscending ? parseFloat(cellA) - parseFloat(cellB) : parseFloat(cellB) - parseFloat(cellA);
                }
            });

            // Remove existing rows and add sorted rows
            rows.forEach(row => table.tBodies[0].appendChild(row));
            
            // Toggle sort direction for next click
            table.setAttribute('data-sort-asc', (!isAscending).toString());
        }

        // Initialize chart on page load
        document.addEventListener('DOMContentLoaded', initChart);
    </script>
</body>
</html>