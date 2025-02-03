import pandas as pd
import re

def clean_movies_csv(input_file, output_file):
    df = pd.read_csv(input_file)

    # extract year from the title col using regex ((\d{4}) represents year bit), add to year col
    for index, row in df.iterrows():
        match = re.search(r'\((\d{4})\)\s*$', row['title']) # works even if trailing spaces at end
        if match:
            df.at[index, 'year'] = int(match.group(1))
        else:
            df.at[index, 'year'] = None
            print(f"Movie ID {row['movieId']} does not have a year in the title.")  # in case of error

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

    # split genres into multiple rows
    df = df.assign(genre=df['genres'].str.split('|')).explode('genre').drop('genres', axis=1)

    # add a decade column (not sure if we need this)
    df['decade'] = (df['year'] // 10) * 10

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
