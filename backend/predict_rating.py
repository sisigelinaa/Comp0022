from flask import Flask, request, jsonify
import pandas as pd
import numpy as np
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.feature_extraction.text import TfidfVectorizer
from scipy.sparse.linalg import svds
from scipy.sparse import csr_matrix
import logging
import sys

app = Flask(__name__)

# App Logger used for debugging
app.logger.addHandler(logging.StreamHandler(sys.stdout))
app.logger.setLevel(logging.INFO)

# Load movie dataset
movies = pd.read_csv("csv/movies.csv")
ratings = pd.read_csv("csv/ratings.csv") 

# Ensure missing values are handled
to_fill = {"genres": "", "directors": "", "actors": "", "language": "", "year": "", "runtime": 0, "imdbRating": 0}
movies.fillna(to_fill, inplace=True)

# 1. Content-Based Filtering (CBF)
def content_based_prediction(new_movie):
    app.logger.info("Running Content-Based Filtering Prediction...")

    # Ensure all necessary columns exist in the dataset
    if not all(col in movies.columns for col in ["genres", "directors", "actors", "language", "year", "runtime", "imdbRating"]):
        app.logger.warning("Required columns missing in movies dataset!")
        return None

    # Handle missing values for the new movie
    new_movie = {key: new_movie.get(key, "") for key in ["genres", "directors", "actors", "language", "year", "runtime"]}

    # Combine all features into a single string
    movies["combined_features"] = (
        movies["genres"] + " " +
        movies["directors"] + " " +
        movies["actors"] + " " +
        movies["language"] + " " +
        movies["year"].astype(str) + " " +
        movies["runtime"].astype(str)
    )

    new_movie_features = (
        new_movie["genres"] + " " +
        new_movie["directors"] + " " +
        new_movie["actors"] + " " +
        new_movie["language"] + " " +
        str(new_movie["year"]) + " " +
        str(new_movie["runtime"])
    )

    tfidf = TfidfVectorizer(stop_words="english")
    tfidf_matrix = tfidf.fit_transform(movies["combined_features"])
    
    new_movie_vec = tfidf.transform([new_movie_features])
    similarities = cosine_similarity(new_movie_vec, tfidf_matrix).flatten()
    
    top_movies = np.argsort(similarities)[::-1][:5]
    predicted_rating = np.average(movies.iloc[top_movies]["imdbRating"], weights=similarities[top_movies])
    
    return round(predicted_rating, 2) if not np.isnan(predicted_rating) else None

# 2. Collaborative Filtering (CF) using SVD
def collaborative_filtering_prediction(user_id, new_movie_id):
    app.logger.info("Running Collaborative Filtering Prediction...")

    if "userId" not in ratings.columns or "movieId" not in ratings.columns or "rating" not in ratings.columns:
        app.logger.warning("Missing required columns in ratings dataset!")
        return None

    ratings["movieId"] = ratings["movieId"].astype(int)
    ratings["userId"] = ratings["userId"].astype(int)

    try:
        new_movie_id = int(new_movie_id)
    except ValueError:
        app.logger.error(f"Invalid movie ID format: {new_movie_id}")
        return None

    # Create user-movie matrix
    user_movie_ratings = ratings.pivot(index="userId", columns="movieId", values="rating").fillna(0)
    sparse_ratings = csr_matrix(user_movie_ratings.astype(np.float32))

    if new_movie_id not in user_movie_ratings.columns:
        app.logger.warning(f"Movie ID {new_movie_id} not found in dataset!")
        return None

    try:
        k_value = min(50, min(sparse_ratings.shape) - 1)
        U, sigma, Vt = svds(sparse_ratings, k=k_value)
        sigma = np.diag(sigma)
        predicted_ratings = np.dot(np.dot(U, sigma), Vt)
        pred_df = pd.DataFrame(predicted_ratings, index=user_movie_ratings.index, columns=user_movie_ratings.columns)

        if user_id not in pred_df.index:
            return None

        rating_prediction = float(pred_df.loc[user_id, new_movie_id])
        return round(rating_prediction, 2) if not np.isnan(rating_prediction) else None

    except Exception as e:
        app.logger.error(f"SVD Error: {e}")
        return None

@app.route("/predict", methods=["POST"])
def predict():
    data = request.json  # Expecting { "movie_id": 123, "genres": "Sci-Fi", "directors": "Nolan", 
                         # "year": "2014", "actors": "Matthew McConaughey", "runtime": 169, "language": "English" }

    # Validate input (At least movie_id, genres, and directors are required)
    if not data or "movie_id" not in data or "genres" not in data or "directors" not in data:
        return jsonify({"error": "Missing 'movie_id', 'genres', or 'directors' in request"}), 400

    # Ensure all optional fields have a default value if missing
    data["year"] = data.get("year", "")
    data["actors"] = data.get("actors", "")
    data["runtime"] = data.get("runtime", 0)  # Convert runtime to integer
    data["language"] = data.get("language", "")

    # Get Content-Based Prediction
    cb_pred = content_based_prediction(data)
    cb_pred = float(cb_pred) if isinstance(cb_pred, np.float32) else cb_pred

    # Get Collaborative Filtering Prediction
    cf_pred = collaborative_filtering_prediction(user_id=1, new_movie_id=data["movie_id"])
    cf_pred = float(cf_pred) if isinstance(cf_pred, np.float32) else cf_pred 

    # Handle "N/A" cases correctly
    if cf_pred == "N/A":
        cf_pred = None
    if cb_pred == "N/A":
        cb_pred = None

    # Hybrid Prediction (Average of both if possible)
    if cf_pred is not None and cb_pred is not None:
        hybrid_pred = round((cb_pred + cf_pred) / 2, 2)
    elif cf_pred is not None:
        hybrid_pred = cf_pred
    else:
        hybrid_pred = cb_pred

    # Return the JSON response
    return jsonify({
        "content_based": cb_pred,
        "collaborative": cf_pred,
        "hybrid": hybrid_pred
    })

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True, use_reloader=False)
