import pandas as pd
import re
import csv
import time
from imdb import IMDb

def clean_movies_csv(input_file, output_file):
    df = pd.read_csv(input_file)

    # extract year from the title col using regex ((\d{4}) represents year bit), add to year col
    for index, row in df.iterrows():
        match = re.search(r'\((\d{4})\)\s*$', row['title']) # works even if trailing spaces at end
        if match:
            df.at[index, 'year'] = int(match.group(1))
        else:
            df.at[index, 'year'] = None

    # remove year from title string
    df['title'] = df['title'].apply(lambda x: re.sub(r' \(\d{4}\)\s*$', '', x))

    # convert year col to ints (because None values make col float)
    df['year'] = pd.to_numeric(df['year'], errors='coerce').fillna(-1).astype(int)
    
    # handle rows with year = -1 (invalid year values)
    missing_years = df[df['year'] == -1]
    if not missing_years.empty:
        print("Movies with missing years:")
        print(missing_years[['movieId', 'title', 'genres']])
        # drop these rows if there are any as we can't use incomplete data
        df = df[df['year'] != -1]

    # TODO: task 2 uses this, revert? although
    # # split genres into multiple rows
    # df = df.assign(genre=df['genres'].str.split('|')).explode('genre').drop('genres', axis=1)

    # # add a decade column (not sure if we need this)
    # df['decade'] = (df['year'] // 10) * 10

    df.to_csv(output_file, index=False)
    print(f"Cleaned data saved to {output_file}")

def clean_ratings_csv(input_file, output_file):
    df = pd.read_csv(input_file)

    # convert the 'timestamp' column to a readable date and time
    df['timestamp'] = pd.to_datetime(df['timestamp'], unit='s')

    df.to_csv(output_file, index=False)
    print(f"Cleaned ratings data saved to {output_file}")

def clean_links_csv(input_file, output_file):
    df = pd.read_csv(input_file)

    # fill missing tmdbId values with -1
    df['tmdbId'] = df['tmdbId'].fillna(-1)

    # convert tmdbId to integer
    df['tmdbId'] = df['tmdbId'].astype(int)

    # remove duplicate movieId entries
    df = df.drop_duplicates(subset='movieId')

    df.to_csv(output_file, index=False)
    print(f"Cleaned links data saved to {output_file}")

def clean_tags_csv(input_file, output_file):
    df = pd.read_csv(input_file)

    # convert the 'timestamp' column to a readable date format
    df['timestamp'] = pd.to_datetime(df['timestamp'], unit='s')

    # remove exact duplicate rows
    df = df.drop_duplicates()

    df.to_csv(output_file, index=False)
    print(f"Cleaned tags data saved to {output_file}")

# Input and output file paths
movies_input_file = './csv/movies.csv'
movies_output_file = './csv/movies_cleaned.csv'
ratings_input_file = './csv/ratings.csv'
ratings_output_file = './csv/ratings_cleaned.csv'
links_input_file = './csv/links.csv'
links_output_file = './csv/links_cleaned.csv'
tags_input_file = './csv/tags.csv'
tags_output_file = './csv/tags_cleaned.csv'

# Run the cleaning functions
clean_movies_csv(movies_input_file, movies_output_file)
clean_ratings_csv(ratings_input_file, ratings_output_file)
clean_links_csv(links_input_file, links_output_file)
clean_tags_csv(tags_input_file, tags_output_file)

print("Preprocessing complete. Cleaned files saved.")


def add_actors_and_directors(movies_input_file, links_input_file, actors_output_file, directors_output_file, actors_movies_output_file, directors_movies_output_file):
    # Initialize IMDbPY
    ia = IMDb()

    # Load movies and links data
    movies = {}
    with open(movies_input_file, "r", encoding="utf-8") as file:
        reader = csv.DictReader(file)
        for row in reader:
            movies[row["movieId"]] = row

    links = {}
    with open(links_input_file, "r", encoding="utf-8") as file:
        reader = csv.DictReader(file)
        for row in reader:
            links[row["movieId"]] = row["imdbId"]

    actors_csv_dict = {}
    directors_csv_dict = {}
    actors_movies_ids_csv_dict = {}
    directors_movies_ids_csv_dict = {}

    # Fetch actors and directors
    print("Fetching actors and directors for movies...")
    i = 0
    for movieId, imdbId in links.items():
        i += 1
        if i > 10:  # TODO: remove this break when done testing, to process all 10000 records
            break
        print(f"{i}/{len(links)}: Fetching data for IMDb ID {imdbId}...")

        try:
            movie = ia.get_movie(imdbId)
            actors = [actor["name"] for actor in movie.get("cast", [])[:5]]
            directors = [director["name"] for director in movie.get("directors", [])]

            movies[movieId]["actors"] = "|".join(actors)
            movies[movieId]["directors"] = "|".join(directors)

            for actor in actors:
                if actor not in actors_csv_dict:
                    actors_csv_dict[actor] = len(actors_csv_dict) + 1
                # linking ids
                unique_id = len(actors_movies_ids_csv_dict) + 1
                actor_id = actors_csv_dict[actor]
                actors_movies_ids_csv_dict[unique_id] = [actor_id, movieId]
                # TODO: add actor info

            for director in directors:
                if director not in directors_csv_dict:
                    directors_csv_dict[director] = len(directors_csv_dict) + 1
                # linking ids
                unique_id = len(directors_movies_ids_csv_dict) + 1
                director_id = directors_csv_dict[director]
                directors_movies_ids_csv_dict[unique_id] = [director_id, movieId]
                # TODO: add director info

        except Exception as e:
            print(f"Failed to fetch data for IMDb ID {imdbId}: {e}")

        time.sleep(0.5)  # Sleep to avoid rate limiting

    # Write updated movies data back to file
    with open(movies_input_file, "w", newline="", encoding="utf-8") as file:
        fieldnames = ["movieId", "title", "genres", "year", "actors", "directors"]
        writer = csv.DictWriter(file, fieldnames=fieldnames)
        
        writer.writeheader()
        for movie in movies.values():
            writer.writerow(movie)

    print(f"Updated movies data saved to {movies_input_file}")

    # Write actors CSV
    with open(actors_output_file, "w", newline="", encoding="utf-8") as file:
        writer = csv.writer(file)
        writer.writerow(["actor_id", "actor_name"])
        for actor, actor_id in actors_csv_dict.items():
            writer.writerow([actor_id, actor])

    print(f"Actors data saved to {actors_output_file}")

    # Write directors CSV
    with open(directors_output_file, "w", newline="", encoding="utf-8") as file:
        writer = csv.writer(file)
        writer.writerow(["director_id", "director_name"])
        for director, director_id in directors_csv_dict.items():
            writer.writerow([director_id, director])

    print(f"Directors data saved to {directors_output_file}")

    # Write actor-movie relationships CSV
    with open(actors_movies_output_file, "w", newline="", encoding="utf-8") as file:
        writer = csv.writer(file)
        writer.writerow(["unique_id", "actor_id", "movie_id"])
        for unique_id, ids in actors_movies_ids_csv_dict.items():
            writer.writerow([unique_id, ids[0], ids[1]])

    print(f"Actor-movie id pairs saved to {actors_movies_output_file}")

    # Write director-movie relationships CSV
    with open(directors_movies_output_file, "w", newline="", encoding="utf-8") as file:
        writer = csv.writer(file)
        writer.writerow(["unique_id", "director_id", "movie_id"])
        for unique_id, ids in directors_movies_ids_csv_dict.items():
            writer.writerow([unique_id, ids[0], ids[1]])

    print(f"Director-movie id pairs saved to {directors_movies_output_file}")


add_actors_and_directors("./csv/movies_cleaned.csv", "./csv/links_cleaned.csv",
                         "./csv/actors.csv", "./csv/directors.csv", "./csv/actors_movies.csv", "./csv/directors_movies.csv")
print("Actors, directors, and actor-movie and director-movie relationship files saved successfully!")
