import pandas as pd


def clean_personality_data(input_file, output_file):
    df = pd.read_csv(input_file)

    # Remove spaces after commas in all columns (spaces will only have remained in string columns)
    for col in df.columns:
        if df[col].dtype == "object":  # Apply only to string columns
            df[col] = df[col].apply(lambda x: x.strip())
    # remove spaces in columns names too
    df.columns = df.columns.str.strip()

    df = df.rename(
        columns={
            "userid": "userId",
            "emotional_stability": "emotionalStability",
            "assigned metric": "assignedMetric",
            "assigned condition": "assignedCondition",
            "movie_1": "movie1",
            "predicted_rating_1": "predictedRating1",
            "movie_2": "movie2",
            "predicted_rating_2": "predictedRating2",
            "movie_3": "movie3",
            "predicted_rating_3": "predictedRating3",
            "movie_4": "movie4",
            "predicted_rating_4": "predictedRating4",
            "movie_5": "movie5",
            "predicted_rating_5": "predictedRating5",
            "movie_6": "movie6",
            "predicted_rating_6": "predictedRating6",
            "movie_7": "movie7",
            "predicted_rating_7": "predictedRating7",
            "movie_8": "movie8",
            "predicted_rating_8": "predictedRating8",
            "movie_9": "movie9",
            "predicted_rating_9": "predictedRating9",
            "movie_10": "movie10",
            "predicted_rating_10": "predictedRating10",
            "movie_11": "movie11",
            "predicted_rating_11": "predictedRating11",
            "movie_12": "movie12",
            "predicted_rating_12": "predictedRating12",
            "is_personalized": "isPersonalized",
            "enjoy_watching": "enjoyWatching",
        }
    )

    # Add a unique ID column (row number)
    df = df.reset_index().rename(columns={"index": "uniqueId"})

    # Save to CSV
    df.to_csv(output_file, index=False)
    print(f"Cleaned user data saved to {output_file}")


def clean_ratings_data(input_file, output_file):
    # Load the CSV file
    df = pd.read_csv(input_file)

    # Remove spaces after commas in all columns (spaces will only have remained in string columns)
    for col in df.columns:
        if df[col].dtype == "object":  # Apply only to string columns
            df[col] = df[col].apply(lambda x: x.strip())
    # remove spaces in columns names too
    df.columns = df.columns.str.strip()

    print(df.columns)

    # Rename columns for clarity
    df = df.rename(
        columns={"useri": "userId", "movie_id": "movieId", "tstamp": "timestamp"}
    )

    # Add a unique ID column (row number)
    df = df.reset_index().rename(columns={"index": "uniqueId"})

    # Save to CSV
    df.to_csv(output_file, index=False)
    print(f"Cleaned ratings data saved to {output_file}")


# Input and output file paths
personality_input_file = "./csv/raw_csv/personality-data_raw.csv"
personality_output_file = "./csv/personality_data.csv"
ratings_input_file = "./csv/raw_csv/ratings_personality_raw.csv"
ratings_output_file = "./csv/ratings_personality.csv"

# Run the cleaning functions
clean_personality_data(personality_input_file, personality_output_file)
clean_ratings_data(ratings_input_file, ratings_output_file)

print("Preprocessing complete. Cleaned files saved.")
