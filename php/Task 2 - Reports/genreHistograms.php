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

$conn->close();

// Determine sorting type
$type = $_GET['type'] ?? 'popularity';
if ($type === 'popularity') {
    usort($report, fn($a, $b) => $b['average'] <=> $a['average']);
    $chartTitle = "Genre Popularity (Average IMDb Rating)";
    $dataPoints = array_map(fn($item) => $item['average'], $report);
} else {
    usort($report, fn($a, $b) => $b['stddev'] <=> $a['stddev']);
    $chartTitle = "Genre Polarization (Rating Std. Dev.)";
    $dataPoints = array_map(fn($item) => $item['stddev'], $report);
}
$labels = array_map(fn($item) => $item['genre'], $report);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $chartTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-dark text-light">
    <div class="container mt-4">
        <a href="../Task 1 - Dashboard/dashboard.php" class="btn btn-light mb-3">← Back to Search</a>
        <h1 class="text-center mb-4"><?php echo $chartTitle; ?></h1>
        <canvas id="genreChart"></canvas>
    </div>
    <script>
        var ctx = document.getElementById('genreChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: '<?php echo $chartTitle; ?>',
                    data: <?php echo json_encode($dataPoints); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>
