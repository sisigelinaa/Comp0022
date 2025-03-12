<?php
// genre_report.php - This file now correctly displays the genre report as a table.

$servername = "db";
$username = "user";
$password = "password";
$database = "movielens";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
}

// Fetch movies that have ratings and genre information
$sql = "SELECT genres, imdbRating FROM movies WHERE imdbRating IS NOT NULL AND genres <> ''";
$result = $conn->query($sql);

$genreData = [];
while ($row = $result->fetch_assoc()) {
    $genres = explode(',', $row['genres']);
    $rating = floatval($row['imdbRating']);
    foreach ($genres as $genre) {
        $genre = trim($genre);
        if ($genre === '') continue;
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
        'genre'   => $genre,
        'count'   => $count,
        'average' => round($avg, 2),
        'stddev'  => round($stddev, 2)
    ];
}

// Sort data for table display
usort($report, function($a, $b) { return $b['count'] <=> $a['count']; });

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genre Reports (Table)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<div class="container mt-4">
    <h1 class="text-center mb-4">Genre Reports (Table)</h1>

    <h2>Popularity Report</h2>
    <table class="table table-dark table-striped">
        <thead>
            <tr>
                <th>Genre</th>
                <th>Movie Count</th>
                <th>Average Rating</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($report as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['genre']); ?></td>
                <td><?php echo $row['count']; ?></td>
                <td><?php echo $row['average']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2 class="mt-5">Polarization Report</h2>
    <table class="table table-dark table-striped">
        <thead>
            <tr>
                <th>Genre</th>
                <th>Rating Standard Deviation</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($report as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['genre']); ?></td>
                <td><?php echo $row['stddev']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="text-center mt-4">
        <a href="index.php" class="btn btn-warning">Back to Movie Finder</a>
    </div>
</div>
</body>
</html>
