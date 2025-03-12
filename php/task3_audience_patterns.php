<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Genre Correlations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

<div class="container mt-4">
    <h1 class="text-center">🎬 Genre Correlations</h1>

    <!-- Back to Dashboard Button -->
    <div class="text-center my-4">
        <a href="/" class="btn btn-primary">Back to Dashboard</a>
    </div>

    <!-- Correlation Table -->
    <table class="table table-dark table-striped">
        <thead>
            <tr>
                <th>Genre A</th>
                <th>Genre B</th>
                <th>Correlation</th>
                <th>p-value</th>
                <th>Number of Users</th>
            </tr>
        </thead>
        <tbody>
            {% for correlation in correlations %}
            <tr>
                <td>{{ correlation['genre_a'] }}</td>
                <td>{{ correlation['genre_b'] }}</td>
                <td>{{ correlation['correlation'] | round(2) }}</td>
                <td>{{ correlation['p_value'] | round(4) }}</td>
                <td>{{ correlation['num_users'] }}</td>
            </tr>
            {% endfor %}
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>