# import mysql.connector
# import pandas as pd
# import matplotlib.pyplot as plt
# import time

# db_config = {
#     'host': 'db', 
#     'user': 'root',
#     'password': '***',  # replace *** with your MySQL root password
#     'database': 'movielens'
# }

# # Wait for MySQL to be ready
# def wait_for_db():
#     for attempt in range(10):  # Retry up to 10 times
#         try:
#             connection = mysql.connector.connect(**db_config)
#             connection.close()
#             print("Database is ready!")
#             return
#         except mysql.connector.Error as err:
#             print(f"Attempt {attempt + 1}: Database not ready, retrying in 5 seconds...")
#             time.sleep(5)
#     raise Exception("Database is not ready after 10 attempts.")


# wait_for_db()

# # Connect to the database
# try:
#     connection = mysql.connector.connect(**db_config)
#     cursor = connection.cursor()

#     # Query 1: Genre Popularity (Top Genres by Average Rating)
#     query_popularity = """
#     SELECT 
#         m.genre, 
#         COUNT(r.rating) AS total_ratings, 
#         AVG(r.rating) AS avg_rating
#     FROM 
#         movies m
#     JOIN 
#         ratings r ON m.movieId = r.movieId
#     GROUP BY 
#         m.genre
#     ORDER BY 
#         avg_rating DESC;
#     """

#     # Query 2: Genre Polarization (Genres with Highest StdDev in Ratings)
#     query_polarization = """
#     SELECT 
#         m.genre, 
#         STDDEV(r.rating) AS rating_stddev,
#         COUNT(r.rating) AS total_ratings
#     FROM 
#         movies m
#     JOIN 
#         ratings r ON m.movieId = r.movieId
#     GROUP BY 
#         m.genre
#     HAVING 
#         COUNT(r.rating) > 10  -- Ignore genres with too few ratings
#     ORDER BY 
#         rating_stddev DESC;
#     """

#     # run queries and fetch results
#     cursor.execute(query_popularity)
#     popularity_data = cursor.fetchall()

#     cursor.execute(query_polarization)
#     polarization_data = cursor.fetchall()

#     # convert results to pd df and save to csv
#     columns_popularity = ['Genre', 'Total Ratings', 'Average Rating']
#     columns_polarization = ['Genre', 'Rating StdDev', 'Total Ratings']

#     popularity_df = pd.DataFrame(popularity_data, columns=columns_popularity)
#     polarization_df = pd.DataFrame(polarization_data, columns=columns_polarization)

#     popularity_df.to_csv('./csv/genre_popularity.csv', index=False)
#     polarization_df.to_csv('./csv/genre_polarization.csv', index=False)

#     # plot results
#     plt.figure(figsize=(10, 5))
#     popularity_df.head(10).plot(kind='bar', x='Genre', y='Average Rating', legend=False)
#     plt.title("Top 10 Most Popular Genres (By Average Rating)")
#     plt.ylabel("Average Rating")
#     plt.xticks(rotation=45)
#     plt.show()

#     plt.figure(figsize=(10, 5))
#     polarization_df.head(10).plot(kind='bar', x='Genre', y='Rating StdDev', legend=False)
#     plt.title("Top 10 Most Polarizing Genres (By Rating StdDev)")
#     plt.ylabel("Rating Standard Deviation")
#     plt.xticks(rotation=45)
#     plt.show()

#     print("Genre reports generated and saved!")

# except mysql.connector.Error as err:
#     print(f"Error: {err}")
# finally:
#     if 'cursor' in locals():
#         cursor.close()
#     if 'connection' in locals():
#         connection.close()
