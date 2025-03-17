import pandas as pd
import os

# Load movies dataset
movies_file = r"/Users/peterpetrov/Downloads/Comp0022/csv/movies.csv"
movies = pd.read_csv(movies_file)

# Ensure output directory exists
output_dir = "normalized_csv"
os.makedirs(output_dir, exist_ok=True)

# Normalize actors
if "actors" in movies.columns:
    actors_movies = movies[['movieId', 'actors']].dropna()
    actors_movies = actors_movies.assign(actors=actors_movies['actors'].str.split('|')).explode('actors')
    actors_movies = actors_movies.rename(columns={'actors': 'actorName'})
    actors_movies.to_csv(os.path.join(output_dir, "actors_movies.csv"), index=False)
    movies.drop(columns=['actors'], inplace=True)

# Normalize directors
if "directors" in movies.columns:
    directors_movies = movies[['movieId', 'directors']].dropna()
    directors_movies = directors_movies.assign(directors=directors_movies['directors'].str.split('|')).explode('directors')
    directors_movies = directors_movies.rename(columns={'directors': 'directorName'})
    directors_movies.to_csv(os.path.join(output_dir, "directors_movies.csv"), index=False)
    movies.drop(columns=['directors'], inplace=True)


# Normalize genres (with unique ID)
if "genres" in movies.columns:
    genres_movies = movies[['movieId', 'genres']].dropna()
    genres_movies = genres_movies.assign(genres=genres_movies['genres'].str.split('|')).explode('genres')

    # Create unique genres table
    unique_genres = genres_movies[['genres']].drop_duplicates().reset_index(drop=True)
    unique_genres['genreId'] = range(1, len(unique_genres) + 1)

    # Merge genreId into genres_movies and add uniqueId
    genres_movies = genres_movies.merge(unique_genres, on="genres", how="left")
    genres_movies = genres_movies[['movieId', 'genreId']].reset_index(drop=True)
    genres_movies.insert(0, 'uniqueId', genres_movies.index + 1)  # Add uniqueId

    # Save files (with genreId first)
    unique_genres = unique_genres[['genreId', 'genres']]
    unique_genres.to_csv(os.path.join(output_dir, "genres.csv"), index=False)
    genres_movies.to_csv(os.path.join(output_dir, "genres_movies.csv"), index=False)
    movies.drop(columns=['genres'], inplace=True)

# Normalize languages (with unique ID)
if "language" in movies.columns:
    languages_movies = movies[['movieId', 'language']].dropna()
    languages_movies = languages_movies.assign(language=languages_movies['language'].str.split('|')).explode('language')

    # Create unique languages table
    unique_languages = languages_movies[['language']].drop_duplicates().reset_index(drop=True)
    unique_languages['languageId'] = range(1, len(unique_languages) + 1)

    # Merge languageId into languages_movies and add uniqueId
    languages_movies = languages_movies.merge(unique_languages, on="language", how="left")
    languages_movies = languages_movies[['movieId', 'languageId']].reset_index(drop=True)
    languages_movies.insert(0, 'uniqueId', languages_movies.index + 1)  # Add uniqueId

    # Save files (with languageId first)
    unique_languages = unique_languages[['languageId', 'language']]
    unique_languages.to_csv(os.path.join(output_dir, "languages.csv"), index=False)
    languages_movies.to_csv(os.path.join(output_dir, "languages_movies.csv"), index=False)
    movies.drop(columns=['language'], inplace=True)

# Save the cleaned movies dataset
movies.to_csv(os.path.join(output_dir, "movies_normalized.csv"), index=False)

print("Normalization complete. Normalized files are saved in 'normalized_csv' folder.")
