from flask import jsonify
from db_connection import get_db_connection

def genre_popularity():
    conn = get_db_connection()
    cursor = conn.cursor()

    sql = """
    SELECT genre, COUNT(rating) AS total_ratings, AVG(rating) AS avg_rating
    FROM movies m
    JOIN ratings r ON m.movieId = r.movieId
    GROUP BY genre
    ORDER BY avg_rating DESC
    """
    cursor.execute(sql)
    results = cursor.fetchall()

    cursor.close()
    conn.close()

    return jsonify(results)

def genre_polarization():
    conn = get_db_connection()
    cursor = conn.cursor()

    sql = """
    SELECT genre, STDDEV(rating) AS rating_stddev, COUNT(rating) AS total_ratings
    FROM movies m
    JOIN ratings r ON m.movieId = r.movieId
    GROUP BY genre
    HAVING COUNT(rating) > 10
    ORDER BY rating_stddev DESC
    """
    cursor.execute(sql)
    results = cursor.fetchall()

    cursor.close()
    conn.close()

    return jsonify(results)