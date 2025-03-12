# Task 3 - Analysis of Audience Rating Patterns

# for each user, if they give low ratings, see if they give low ratings in general, or if they are specific to certain genres


# for each segment of users that rate a genre highly, see if they rate other genres highly as well

# genre_correlation_analysis.py
import os
import pandas as pd
import numpy as np
from itertools import permutations
from scipy.stats import pearsonr
from flask import Flask, jsonify
from dotenv import load_dotenv
import mysql.connector

load_dotenv()
app = Flask(__name__)

# setup mysql connection
db_connection = mysql.connector.connect(
    host="mysql_db",
    user="user",
    password="password",
    database="movielens"
)


# calculate correlations between all genre pairs for users who rate genre_a > 7
def get_genre_correlations():
    # step 1: load and preprocess data
    movies = pd.read_sql("SELECT movieId, genres FROM movies", db_connection)
    ratings = pd.read_sql("SELECT userId, movieId, rating FROM ratings", db_connection)

    # explode genres into individual rows
    movies_exploded = movies.assign(genres=movies['genres'].str.split('|')).explode('genres')

    # merge, then calc avg rating per user per genre
    user_genre_avg = pd.merge(ratings, movies_exploded, on='movieId') \
        .groupby(['userId', 'genres'])['rating'].mean().reset_index()
    print(user_genre_avg)

    # step 2: generate genre pairs
    genres = user_genre_avg['genres'].unique().tolist()
    genre_pairs = list(permutations(genres, 2))

    # step 3: analyze correlations
    MIN_USERS = 10      # don't consider pairs with less than 10 users
    results = []

    for genre_a, genre_b in genre_pairs:
        # filter users who rated genre_a > 7
        users_a = user_genre_avg[
            (user_genre_avg['genres'] == genre_a) & 
            (user_genre_avg['rating'] > 7)
        ]['userId'].unique()

        # get common users who also rated genre_b
        users_b = user_genre_avg[
            (user_genre_avg['genres'] == genre_b)
        ]['userId'].unique()
        common_users = np.intersect1d(users_a, users_b, assume_unique=True)

        if len(common_users) < MIN_USERS:
            continue

        # get ratings for genre_a and genre_b
        df_a = user_genre_avg[
            (user_genre_avg['genres'] == genre_a) & 
            (user_genre_avg['userId'].isin(common_users))
        ]
        df_b = user_genre_avg[
            (user_genre_avg['genres'] == genre_b) & 
            (user_genre_avg['userId'].isin(common_users))
        ]

        merged = pd.merge(df_a, df_b, on='userId', suffixes=('_a', '_b'))
        if len(merged) < MIN_USERS:
            continue

        # get correlation
        corr, p_value = pearsonr(merged['rating_a'], merged['rating_b'])

        results.append({
            'genre_a': genre_a,
            'genre_b': genre_b,
            'correlation': corr,
            'p_value': p_value,
            'num_users': len(merged)
        })

    return pd.DataFrame(results)


# flask endpoint, to give results
@app.route('/api/genre-correlations', methods=['GET'])
def genre_correlations():
    results_df = get_genre_correlations()
    return jsonify(results_df.to_dict(orient='records'))


# test
def visualize_correlations():
    results_df = get_genre_correlations()
    corr_matrix = results_df.pivot(index='genre_a', columns='genre_b', values='correlation')

    import plotly.express as px
    fig = px.imshow(
        corr_matrix,
        labels=dict(x="Genre B", y="Genre A", color="Correlation"),
        title="Genre Correlation Analysis (Users Rating Genre A > 2.5)"
    )
    fig.show()


if __name__ == "__main__":
    # visualize_correlations()
    
    # run flask app
    app.run(host='0.0.0.0', port=5000, debug=True)
