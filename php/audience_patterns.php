<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Genre Correlations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-dark text-light">

<div class="container mt-4">
    <?php
    // ================== MySQL DATA PROCESSING ==================
    $conn = new mysqli("db", "user", "password", "movielens");
    
    if ($conn->connect_error) {
        die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
    }

    // Get user averages per genre using SQL
    $query = "SELECT r.userId, 
                SUBSTRING_INDEX(SUBSTRING_INDEX(m.genres, '|', n.n), '|', -1) AS genre,
                AVG(r.rating) AS avg_rating
              FROM ratings r
              JOIN movies m ON r.movieId = m.movieId
              JOIN (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) n
                ON CHAR_LENGTH(m.genres) - CHAR_LENGTH(REPLACE(m.genres, '|', '')) >= n.n - 1
              GROUP BY r.userId, genre";
    
    $result = $conn->query($query);
    
    // Process results into array
    $genreRatings = [];
    while ($row = $result->fetch_assoc()) {
        $genreRatings[$row['genre']][$row['userId']] = (float)$row['avg_rating'];
    }

    // Get unique genres and prepare results
    $genres = array_keys($genreRatings);
    $results = [];

    foreach ($genres as $genreA) {
        foreach ($genres as $genreB) {
            if ($genreA === $genreB) continue;
            
            // Filter users with ratings > 3.5 in genreA
            $usersA = array_filter(
                $genreRatings[$genreA], 
                fn($rating) => $rating > 3.5
            );
            
            $commonUsers = array_intersect_key(
                $usersA,
                $genreRatings[$genreB] ?? []
            );
            
            if (count($commonUsers) < 10) continue;

            // Get paired ratings
            $ratingsA = [];
            $ratingsB = [];
            foreach ($commonUsers as $userId => $ratingA) {
                $ratingsA[] = $ratingA;
                $ratingsB[] = $genreRatings[$genreB][$userId];
            }

            // Calculate correlation
            $correlation = pearsonCorrelation($ratingsA, $ratingsB);

            $results[] = [
                'genreA' => $genreA,
                'genreB' => $genreB,
                'correlation' => round($correlation, 2),
                'users' => count($commonUsers)
            ];
        }
    }

    // Pearson correlation function
    function pearsonCorrelation($x, $y) {
        $n = count($x);
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumX2 = array_sum(array_map(fn($v) => $v ** 2, $x));
        $sumY2 = array_sum(array_map(fn($v) => $v ** 2, $y));
        $sumXY = array_sum(array_map(fn($a, $b) => $a * $b, $x, $y));
        
        $numerator = $sumXY - ($sumX * $sumY) / $n;
        $denominator = sqrt(($sumX2 - $sumX ** 2 / $n) * ($sumY2 - $sumY ** 2 / $n));
        
        return $denominator ? $numerator / $denominator : 0;
    }

    $conn->close();
    ?>

    <!-- ================== VISUALIZATION ================== -->
    <h1 class="text-center">🎬 Genre Correlations</h1>
    
    <div class="text-center my-4">
        <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
    </div>

    <div class="card bg-secondary text-light mb-4">
        <div class="card-body text-center">
            <p>Users who rated <span class="genre-a">Genre A</span> highly (average > 3.5/5)</p>
            <p>Correlation with other genres:</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <select id="genreSelect" class="form-select bg-dark text-light">
                <option value="">Select a Genre...</option>
                <?php foreach($genres as $genre): ?>
                    <option value="<?= $genre ?>"><?= $genre ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="chart-container mt-4" style="height: 60vh">
        <canvas id="correlationChart"></canvas>
    </div>

    <script>
        // Pass PHP data to JavaScript
        const correlations = <?= json_encode($results) ?>;
        let chart;

        // Chart configuration
        const config = {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Correlation',
                    data: [],
                    backgroundColor: (ctx) => 
                        ctx.raw >= 0 ? 'rgba(54, 162, 235, 0.8)' : 'rgba(255, 99, 132, 0.8)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: -1,
                        max: 1,
                        ticks: { color: '#fff' },
                        grid: { color: 'rgba(255,255,255,0.1)' }
                    },
                    x: {
                        ticks: { 
                            color: '#fff',
                            autoSkip: false
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => 
                                `Correlation: ${ctx.raw.toFixed(2)} (${ctx.raw.users} users)`
                        }
                    }
                }
            }
        };

        // Initialize chart
        chart = new Chart(document.getElementById('correlationChart'), config);

        // Genre selector handler
        document.getElementById('genreSelect').addEventListener('change', function() {
            const genre = this.value;
            const filtered = correlations.filter(item => item.genreA === genre);
            
            chart.data.labels = filtered.map(item => item.genreB);
            chart.data.datasets[0].data = filtered.map(item => ({
                x: item.genreB,
                y: item.correlation,
                users: item.users
            }));
            
            chart.update();
        });
    </script>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>