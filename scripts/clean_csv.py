import pandas as pd
import re


# clean movies.csv:

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

    # now remove year from the title string
    df['title'] = df['title'].apply(lambda x: re.sub(r' \(\d{4}\)\s*$', '', x))

    # convert year col to ints (because None values make col float)
    df['year'] = pd.to_numeric(df['year'], errors='coerce').fillna(-1).astype(int)
    # TODO: HANDLE -1 VALUES LATER ON


    # save to new csv file
    df.to_csv(output_file, index=False)


input_file = './csv/movies.csv'
output_file = './csv/movies_cleaned.csv'
clean_movies_csv(input_file, output_file)
