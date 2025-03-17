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
        <a href="../Task 1 - Dashboard/dashboard.php" class="btn btn-light mb-3">← Back to Search</a>

        <?php
        $conn = new mysqli("db", "user", "password", "movielens");

        if ($conn->connect_error) {
            die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
        }

        // Get user averages per genre using SQL
        $query = "SELECT r.userId, 
                    g.genreName AS genre,
                    AVG(r.rating) AS avg_rating
                FROM ratings r
                JOIN movies m ON r.movieId = m.movieId
                JOIN movies_genres mg ON m.movieId = mg.movieId
                JOIN genres g ON mg.genreId = g.genreId
                GROUP BY r.userId, genre";

        $result = $conn->query($query);

        // Process results into array
        $genreRatings = [];
        while ($row = $result->fetch_assoc()) {
            $genre = trim($row['genre']);  // Trim whitespace from genre name
            $genreRatings[$genre][$row['userId']] = (float) $row['avg_rating'];
        }

        // Get unique genres and prepare results
        $genres = array_keys($genreRatings);
        $resultsHigh = [];
        $resultsLow = [];

        foreach ($genres as $genreA) {

            // Filter users with ratings >= 3.5 in genreA
            $usersAHigh = array_filter(
                $genreRatings[$genreA],
                fn($rating) => $rating >= 3.5
            );
            // Filter users with ratings <= 2 in genreA
            $usersALow = array_filter(
                $genreRatings[$genreA],
                fn($rating) => $rating <= 2
            );

            foreach ($genres as $genreB) {
                if ($genreA === $genreB)
                    continue;

                $commonUsersHigh = array_intersect_key(
                    $usersAHigh,
                    $genreRatings[$genreB] ?? []
                );
                $commonUsersLow = array_intersect_key(
                    $usersALow,
                    $genreRatings[$genreB] ?? []
                );

                if (count($commonUsersHigh) > 10) {
                    // Get paired ratings
                    $ratingsAHigh = [];
                    $ratingsBHigh = [];
                    foreach ($commonUsersHigh as $userId => $ratingA) {
                        $ratingsAHigh[] = $ratingA;
                        $ratingsBHigh[] = $genreRatings[$genreB][$userId];
                    }

                    // Calculate correlation
                    $correlationHigh = pearsonCorrelation($ratingsAHigh, $ratingsBHigh);

                    $resultsHigh[] = [
                        'genreA' => $genreA,
                        'genreB' => $genreB,
                        'correlation' => round($correlationHigh, 2),
                        'users' => count($commonUsersHigh)
                    ];
                }

                if (count($commonUsersLow) > 10) {
                    // Get paired ratings
                    $ratingsALow = [];
                    $ratingsBLow = [];
                    foreach ($commonUsersLow as $userId => $ratingA) {
                        $ratingsALow[] = $ratingA;
                        $ratingsBLow[] = $genreRatings[$genreB][$userId];
                    }

                    // Calculate correlation
                    $correlationLow = pearsonCorrelation($ratingsALow, $ratingsBLow);

                    $resultsLow[] = [
                        'genreA' => $genreA,
                        'genreB' => $genreB,
                        'correlation' => round($correlationLow, 2),
                        'users' => count($commonUsersLow)
                    ];
                }
            }
        }

        // Pearson correlation function
        function pearsonCorrelation($x, $y)
        {
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

        <h1 class="text-center">🎬 Genre Correlations</h1>


        <div class="card bg-secondary text-light mb-4">
            <div class="card-body text-center">
                <p>Users who rated selected genre highly (average >= 3.5/5)</p>
                <p>What these users thought of other genres (rating correlation):</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <select id="genreSelectHigh" class="form-select bg-dark text-light">
                    <option value="">Select a Genre...</option>
                    <?php foreach ($genres as $genre): ?>
                        <option value="<?= $genre ?>"><?= $genre ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="chart-container mt-4" style="height: 60vh">
            <canvas id="correlationChartHigh"></canvas>
        </div>

        <div style="height: 40px;"></div>

        <!--  Low chart  -->

        <div class="card bg-secondary text-light mb-4">
            <div class="card-body text-center">
                <p>Users who rated selected genre poorly (average <= 2/5)</p>
                        <p>What these users thought of other genres (rating correlation):</p>
                        <p>(Note: less data available; less people rate poorly)</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <select id="genreSelectLow" class="form-select bg-dark text-light">
                    <option value="">Select a Genre...</option>
                    <?php foreach ($genres as $genre): ?>
                        <option value="<?= $genre ?>"><?= $genre ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="chart-container mt-4" style="height: 60vh">
            <canvas id="correlationChartLow"></canvas>
        </div>

        <div style="height: 40px;"></div>

        <script>
            // Pass PHP data to JavaScript
            const correlationsHigh = <?= json_encode($resultsHigh) ?>;
            const correlationsLow = <?= json_encode($resultsLow) ?>;
            let chartHigh, chartLow;

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
                            grid: { color: 'rgba(255,255,255,0.1)' },
                            title: {
                                display: true,
                                text: 'Correlation Value',
                                color: '#fff'
                            },
                        },
                        x: {
                            ticks: {
                                color: '#fff',
                                autoSkip: false
                            },
                            title: {
                                display: true,
                                text: 'Genres',
                                color: '#fff'
                            },
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const correlation = ctx.raw.y; // Access the correlation value
                                    const users = ctx.raw.users; // Access the number of users
                                    return `Correlation: ${correlation.toFixed(2)} (${users} users)`;
                                }
                            }
                        }
                    }
                }
            };

            // Initialize chart
            chartHigh = new Chart(document.getElementById('correlationChartHigh'), config);
            chartLow = new Chart(document.getElementById('correlationChartLow'), config);

            // Genre selector handler
            document.getElementById('genreSelectHigh').addEventListener('change', function () {
                const genre = this.value;
                const filtered = correlationsHigh.filter(item => item.genreA === genre);

                chartHigh.data.labels = filtered.map(item => item.genreB);
                chartHigh.data.datasets[0].data = filtered.map(item => ({
                    x: item.genreB,
                    y: item.correlation,
                    users: item.users
                }));

                chartHigh.update();
            });
            document.getElementById('genreSelectLow').addEventListener('change', function () {
                const genre = this.value;
                const filtered = correlationsLow.filter(item => item.genreA === genre);

                chartLow.data.labels = filtered.map(item => item.genreB);
                chartLow.data.datasets[0].data = filtered.map(item => ({
                    x: item.genreB,
                    y: item.correlation,
                    users: item.users
                }));

                chartLow.update();
            });
        </script>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>