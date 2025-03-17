<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$result = null;
$movie_id = $genres = $directors = $year = $actors = $runtime = $language = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $movie_id = $_POST["movie_id"] ?? null;
    $genres = $_POST["genres"] ?? null;
    $directors = $_POST["directors"] ?? null;
    $year = $_POST["year"] ?? null;
    $actors = $_POST["actors"] ?? null;
    $runtime = $_POST["runtime"] ?? null;
    $language = $_POST["language"] ?? null;

    // Validate required input
    if (!$movie_id || !$genres || !$directors) {
        die("<p class='text-danger text-center'>Error: Missing movie_id, genres, or directors.</p>");
    }

    // Prepare JSON data (ensure optional fields are included)
    $data = json_encode([
        "movie_id" => $movie_id,
        "genres" => $genres,
        "directors" => $directors,
        "year" => $year,
        "actors" => $actors,
        "runtime" => $runtime,
        "language" => $language
    ]);

    // Send request to Python API
    $ch = curl_init("http://python_api:5000/predict");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

    $response = curl_exec($ch);
    curl_close($ch);

    // Decode response
    $result = json_decode($response, true);

    // Check if API response is valid
    if (!$result || !isset($result["content_based"])) {
        echo "<p class='text-danger text-center'>Error: API response is invalid.</p>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Predict Movie Rating</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .form-container {
            background-color: #1c1c1c;
            padding: 20px;
            border-radius: 10px;
            max-width: 900px;
            margin: auto;
        }
        .form-control {
            min-width: 180px;
            max-width: 220px;
        }
        .btn-warning {
            min-width: 120px;
            font-weight: bold;
        }
        .info-text {
            font-size: 0.85rem;
            color: #bbb;
            font-style: italic;
        }
        .prediction-link {
            color: #ffcc00;
            text-decoration: none;
            font-weight: bold;
        }
        .prediction-link:hover {
            text-decoration: underline;
        }
        .runtime-label {
            font-size: 0.85rem;
            color: #bbb;
        }
    </style>
</head>
<body class="text-light" style="background-color: #141414;">
    <div class="container mt-4">

        <!-- Back Button at the Top -->
        <div class="text-start mb-3">
            <a href="dashboard.php" class="btn btn-outline-light">← Back to Dashboard</a>
        </div>

        <h2 class="text-warning text-center">⭐ Predict Movie Rating</h2>

        <!-- Prediction Form -->
        <form method="POST" class="form-container">
            <div class="row justify-content-center">
                <div class="col-md-3">
                    <input type="text" name="movie_id" class="form-control" placeholder="Movie ID" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="genres" class="form-control" placeholder="Genres" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="directors" class="form-control" placeholder="Directors" required>
                </div>
            </div>

            <div class="row justify-content-center mt-2">
                <div class="col-md-3">
                    <input type="text" name="year" class="form-control" placeholder="Year (Optional)">
                </div>
                <div class="col-md-3">
                    <input type="text" name="actors" class="form-control" placeholder="Actors (Optional)">
                </div>
                <div class="col-md-3">
                    <input type="text" name="runtime" class="form-control" placeholder="Runtime (Optional)">
                    <p class="runtime-label text-center">* Runtime is in minutes</p>
                </div>
                <div class="col-md-3">
                    <input type="text" name="language" class="form-control" placeholder="Language (Optional)">
                </div>
            </div>

            <div class="text-center mt-3">
                <button type="submit" class="btn btn-warning">Predict</button>
            </div>
        </form>

        <!-- Display Predictions -->
        <?php if ($result): ?>
        <div class="d-flex justify-content-center mt-4">
            <div class="card bg-dark text-light p-4 text-center" style="min-width: 400px; max-width: 550px;">
                <!-- Display user input information -->
                <p class="text-light"><strong>Predicting Rating for:</strong></p>
                <p class="mb-1"><strong>Movie ID:</strong> <?= htmlspecialchars($movie_id) ?></p>
                <p class="mb-1"><strong>Genres:</strong> <?= htmlspecialchars($genres) ?></p>
                <p class="mb-1"><strong>Director:</strong> <?= htmlspecialchars($directors) ?></p>
                <?php if (!empty($year)) echo "<p class='mb-1'><strong>Year:</strong> " . htmlspecialchars($year) . "</p>"; ?>
                <?php if (!empty($actors)) echo "<p class='mb-1'><strong>Actors:</strong> " . htmlspecialchars($actors) . "</p>"; ?>
                <?php if (!empty($runtime)) echo "<p class='mb-1'><strong>Runtime:</strong> " . htmlspecialchars($runtime) . " mins</p>"; ?>
                <?php if (!empty($language)) echo "<p class='mb-1'><strong>Language:</strong> " . htmlspecialchars($language) . "</p>"; ?>

                <h3 class="text-warning mt-3"><i class="bi bi-bar-chart-line"></i> Predicted Ratings</h3>
                <p>
                    <strong>
                        <a href="https://www.ibm.com/think/topics/content-based-filtering" target="_blank" class="prediction-link">
                            Content-Based
                        </a>:
                    </strong> <?= $result["content_based"] ?? "N/A"; ?>
                </p>
                <p>
                    <strong>
                        <a href="https://www.ibm.com/think/topics/collaborative-filtering" target="_blank" class="prediction-link">
                            Collaborative Filtering
                        </a>:
                    </strong> <?= $result["collaborative"] ?? "N/A"; ?>
                </p>
                <p class="info-text">* Click on the prediction method names above to learn more about how they work.</p>

                <p class="mt-3">
                    <strong class="text-warning">Final Hybrid Score:</strong> <?= $result["hybrid"] ?? "N/A"; ?>
                    <br><span class="text-secondary">(This score is the combination of the two above)</span>
                </p>
            </div>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>
