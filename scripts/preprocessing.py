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

    # Add a unique ID column (starting from 1)
    df.insert(0, 'uniqueId', range(1, len(df) + 1))

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

    # Add a unique ID column (starting from 1)
    df.insert(0, 'uniqueId', range(1, len(df) + 1))

    df.to_csv(output_file, index=False)
    print(f"Cleaned tags data saved to {output_file}")

# Input and output file paths
movies_input_file = './csv/raw_csv/movies_raw.csv'
movies_output_file = './csv/movies.csv'
ratings_input_file = './csv/raw_csv/ratings_raw.csv'
ratings_output_file = './csv/ratings.csv'
links_input_file = './csv/raw_csv/links_raw.csv'
links_output_file = './csv/links.csv'
tags_input_file = './csv/raw_csv/tags_raw.csv'
tags_output_file = './csv/tags.csv'

# Run the cleaning functions
clean_movies_csv(movies_input_file, movies_output_file)
clean_ratings_csv(ratings_input_file, ratings_output_file)
clean_links_csv(links_input_file, links_output_file)
clean_tags_csv(tags_input_file, tags_output_file)

print("Preprocessing complete. Cleaned files saved.")


def add_actors_and_directors(movies_input_file, links_input_file, actors_output_file, directors_output_file, actors_movies_output_file, directors_movies_output_file):
    # initialize IMDbPY - this allows us to fetch data from IMDb
    ia = IMDb()

    # load movies and links data
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

    # fetch actors and directors
    print("Fetching actors and directors for movies...")
    i = 0
    for movieId, imdbId in links.items():
        i += 1
        # if i > 3:  # TODO: remove this break when done testing, to process all 10000 records
        #     break
        print(f"{i}/{len(links)}: Fetching data for IMDb ID {imdbId}...")

        try:
            # Fetch movie details with additional information
            movie = ia.get_movie(imdbId)

            # Get actors and directors
            actors = movie.get("cast", [])[:5]  
            directors = movie.get("directors", [])

            # Extract relevant details
            movies[movieId]["actors"] = "|".join([actor["name"] for actor in actors])
            movies[movieId]["directors"] = "|".join([director["name"] for director in directors])
            movies[movieId]["runtime"] = movie.get("runtimes", [""])[0]  # Runtime in minutes
            movies[movieId]["language"] = "|".join(movie.get("languages", []))  # Languages
            movies[movieId]["posterUrl"] = movie.get("cover url", "")  # Poster URL
            movies[movieId]["boxOffice"] = movie.get("box office", {}).get("Cumulative Worldwide Gross", "")  # Worldwide earnings

            # Extract critic scores
            movies[movieId]["imdbRating"] = movie.get("rating", "")  # IMDb score
            movies[movieId]["imdbVotes"] = movie.get("votes", "")  # Number of votes

            # Extract awards & nominations
            if "awards" in movie.keys():
                awards_text = movie["awards"]
                awards_list = [a for a in awards_text if "Win" in a or "Nomination" in a]
                movies[movieId]["awards"] = "|".join(awards_list)
            else:
                movies[movieId]["awards"] = ""

            for actor in actors:
                actorId = actor.personID
                if actorId not in actors_csv_dict:
                    actors_csv_dict[actorId] = {
                        "actorName": actor["name"]
                    }   # extendable
                # linking ids for actor-movie relationship
                uniqueId = len(actors_movies_ids_csv_dict) + 1
                actors_movies_ids_csv_dict[uniqueId] = [actorId, movieId]

            for director in directors:
                directorId = director.personID
                if directorId not in directors_csv_dict:
                    directors_csv_dict[directorId] = {
                        "directorName": director["name"]
                    }   # extendable
                # linking ids for director-movie relationship
                uniqueId = len(directors_movies_ids_csv_dict) + 1
                directors_movies_ids_csv_dict[uniqueId] = [directorId, movieId]

        except Exception as e:
            print(f"Failed to fetch data for IMDb ID {imdbId}: {e}")

        time.sleep(0.5)  # Sleep to avoid rate limiting

    # Write updated movies data back to file
    with open(movies_input_file, "w", newline="", encoding="utf-8") as file:
        fieldnames = [
        "movieId", "title", "genres", "year", "actors", "directors",
        "runtime", "language", "posterUrl", "boxOffice", "imdbRating",
        "imdbVotes", "awards"
        ]
        writer = csv.DictWriter(file, fieldnames=fieldnames)
        
        writer.writeheader()
        for movie in movies.values():
            writer.writerow(movie)

    print(f"Updated movies data saved to {movies_input_file}")

    # Write actors CSV
    with open(actors_output_file, "w", newline="", encoding="utf-8") as file:
        writer = csv.writer(file)
        writer.writerow(["actorId", "actorName"])
        for actorId, actor in actors_csv_dict.items():
            writer.writerow([actorId, actor["actorName"]])

    print(f"Actors data saved to {actors_output_file}")

    # Write directors CSV
    with open(directors_output_file, "w", newline="", encoding="utf-8") as file:
        writer = csv.writer(file)
        writer.writerow(["directorId", "directorName"])
        for directorId, director in directors_csv_dict.items():
            writer.writerow([directorId, director["directorName"]])


    print(f"Directors data saved to {directors_output_file}")

    # Write actor-movie relationships CSV
    with open(actors_movies_output_file, "w", newline="", encoding="utf-8") as file:
        writer = csv.writer(file)
        writer.writerow(["uniqueId", "actorId", "movieId"])
        for unique_id, ids in actors_movies_ids_csv_dict.items():
            writer.writerow([unique_id, ids[0], ids[1]])

    print(f"Actor-movie id pairs saved to {actors_movies_output_file}")

    # Write director-movie relationships CSV
    with open(directors_movies_output_file, "w", newline="", encoding="utf-8") as file:
        writer = csv.writer(file)
        writer.writerow(["uniqueId", "directorId", "movieId"])
        for unique_id, ids in directors_movies_ids_csv_dict.items():
            writer.writerow([unique_id, ids[0], ids[1]])

    print(f"Director-movie id pairs saved to {directors_movies_output_file}")


add_actors_and_directors("./csv/movies.csv", "./csv/links.csv",
                         "./csv/actors.csv", "./csv/directors.csv", "./csv/actors_movies.csv", "./csv/directors_movies.csv")
print("Actors, directors, and actor-movie and director-movie relationship files saved successfully!")
