<?php
// genreHistograms.php - Displays histograms for genre popularity or polarization.

$servername = "db";
$username = "user";
$password = "password";
$database = "movielens";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
}

// Fetch movies with ratings and genres
$sql = "SELECT genres, imdbRating FROM movies WHERE imdbRating IS NOT NULL AND genres <> ''";
$result = $conn->query($sql);

$genreData = [];
while ($row = $result->fetch_assoc()) {
    $genres = explode(',', $row['genres']);
    $rating = floatval($row['imdbRating']);
    foreach ($genres as $genre) {
        $genre = trim($genre);
        if ($genre === '')
            continue;
        if (!isset($genreData[$genre])) {
            $genreData[$genre] = [];
        }
        $genreData[$genre][] = $rating;
    }
}

$report = [];
foreach ($genreData as $genre => $ratings) {
    $count = count($ratings);
    $avg = array_sum($ratings) / $count;
    $sumSqDiff = 0;
    foreach ($ratings as $r) {
        $sumSqDiff += pow($r - $avg, 2);
    }
    $stddev = sqrt($sumSqDiff / $count);
    $report[] = [
        'genre' => $genre,
        'count' => $count,
        'average' => round($avg, 2),
        'stddev' => round($stddev, 2)
    ];
}

$conn->close();

$type = $_GET['type'] ?? 'popularity';
if ($type === 'popularity') {
    usort($report, fn($a, $b) => $b['count'] <=> $a['count']);
    $chartTitle = "Genre Popularity (Movie Count)";
    $dataPoints = array_map(fn($item) => $item['count'], $report);
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
        <h1 class="text-center mb-4"><?php echo $chartTitle; ?></h1>
        <canvas id="genreChart"></canvas>
        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-warning">Back to Movie Finder</a>
        </div>
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