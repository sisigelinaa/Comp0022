-- Create Database
CREATE DATABASE IF NOT EXISTS movielens;
USE movielens;

-- Create Movies Table
CREATE TABLE IF NOT EXISTS movies (
    movieId INT PRIMARY KEY,
    title VARCHAR(255),
    genres VARCHAR(255),
    year INT NULL,
    actors VARCHAR(255),
    directors VARCHAR(255),
    runtime INT NULL,
    language VARCHAR(255),
    posterUrl VARCHAR(255),
    boxOffice BIGINT NULL,
    imdbRating FLOAT NULL,
    imdbVotes INT NULL
);

-- Create Links Table
CREATE TABLE IF NOT EXISTS links (
    movieId INT PRIMARY KEY,
    imdbId INT,
    tmdbId INT,
    FOREIGN KEY (movieId) REFERENCES movies(movieId) ON DELETE CASCADE
);

-- Create Ratings Table
CREATE TABLE IF NOT EXISTS ratings (
    uniqueId INT PRIMARY KEY,
    userId INT,
    movieId INT,
    rating FLOAT,
    timestamp VARCHAR(255),
    FOREIGN KEY (movieId) REFERENCES movies(movieId) ON DELETE CASCADE
);

-- Create Tags Table
CREATE TABLE IF NOT EXISTS tags (
    uniqueId INT PRIMARY KEY,
    userId INT,
    movieId INT,
    tag VARCHAR(255),
    timestamp VARCHAR(255),
    FOREIGN KEY (movieId) REFERENCES movies(movieId) ON DELETE CASCADE
);

-- Create Actors Table
CREATE TABLE IF NOT EXISTS actors (
    actorId VARCHAR(255) PRIMARY KEY,
    actorName VARCHAR(255)
);

-- Create Directors Table
CREATE TABLE IF NOT EXISTS directors (
    directorId VARCHAR(255) PRIMARY KEY,
    directorName VARCHAR(255)
);

-- Create ActorsMovies Table
CREATE TABLE IF NOT EXISTS actorsMovies (
    uniqueId INT PRIMARY KEY,
    actorId VARCHAR(255),
    movieId INT,
    FOREIGN KEY (movieId) REFERENCES movies(movieId) ON DELETE CASCADE,
    FOREIGN KEY (actorId) REFERENCES actors(actorId) ON DELETE CASCADE
);

-- Create DirectorsMovies Table
CREATE TABLE IF NOT EXISTS directorsMovies (
    uniqueId INT PRIMARY KEY,
    directorId VARCHAR(255),
    movieId INT,
    FOREIGN KEY (movieId) REFERENCES movies(movieId) ON DELETE CASCADE,
    FOREIGN KEY (directorId) REFERENCES directors(directorId) ON DELETE CASCADE
);

-- Create Personality Data Table
CREATE TABLE IF NOT EXISTS personalityData (
    uniqueId INT PRIMARY KEY,
    userId VARCHAR(255),
    openness FLOAT NULL,
    agreeableness FLOAT NULL,
    emotionalStability FLOAT NULL,
    conscientiousness FLOAT NULL,
    extraversion FLOAT NULL,
    assignedMetric VARCHAR(255),
    assignedCondition VARCHAR(255),
    movie1 INT NULL,
    predictedRating1 FLOAT NULL,
    movie2 INT NULL,
    predictedRating2 FLOAT NULL,
    movie3 INT NULL,
    predictedRating3 FLOAT NULL,
    movie4 INT NULL,
    predictedRating4 FLOAT NULL,
    movie5 INT NULL,
    predictedRating5 FLOAT NULL,
    movie6 INT NULL,
    predictedRating6 FLOAT NULL,
    movie7 INT NULL,
    predictedRating7 FLOAT NULL,
    movie8 INT NULL,
    predictedRating8 FLOAT NULL,
    movie9 INT NULL,
    predictedRating9 FLOAT NULL,
    movie10 INT NULL,
    predictedRating10 FLOAT NULL,
    movie11 INT NULL,
    predictedRating11 FLOAT NULL,
    movie12 INT NULL,
    predictedRating12 FLOAT NULL,
    isPersonalized INT NULL,
    enjoyWatching INT NULL
);

-- Create Ratings Personality Table
CREATE TABLE IF NOT EXISTS ratingsPersonality (
    uniqueId INT PRIMARY KEY,
    userId VARCHAR(255),
    movieId INT NULL,
    rating FLOAT NULL,
    timestamp VARCHAR(255),
    FOREIGN KEY (movieId) REFERENCES movies(movieId) ON DELETE CASCADE

);


CREATE TABLE IF NOT EXISTS users (
    username VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255),
    surname VARCHAR(255),
    gender VARCHAR(255),
    age INT,
    password VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS lists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255),
    title VARCHAR(255),
    description TEXT,
    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS list_movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    list_id INT,
    movie_id INT,
    FOREIGN KEY (list_id) REFERENCES lists(id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(movieId) ON DELETE CASCADE

);

-- Load Movies Data
LOAD DATA INFILE '/var/lib/mysql-files/movies.csv'
INTO TABLE movies
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS
(movieId, title, genres, @year, actors, directors, @runtime, language, posterUrl, @boxOffice, @imdbRating, @imdbVotes)
SET
    year = NULLIF(@year, ''),
    runtime = NULLIF(@runtime, ''),
    boxOffice = NULLIF(@boxOffice, ''),
    imdbRating = NULLIF(@imdbRating, ''),
    imdbVotes = IF(@imdbVotes REGEXP '^[0-9]+$', @imdbVotes, NULL);

-- Load Links Data
LOAD DATA INFILE '/var/lib/mysql-files/links.csv'
INTO TABLE links
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;

-- Load Ratings Data
LOAD DATA INFILE '/var/lib/mysql-files/ratings.csv'
INTO TABLE ratings
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;

-- Load Tags Data
LOAD DATA INFILE '/var/lib/mysql-files/tags.csv'
INTO TABLE tags
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;

-- Load Actors Data
LOAD DATA INFILE '/var/lib/mysql-files/actors.csv'
INTO TABLE actors
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;

-- Load Directors Data
LOAD DATA INFILE '/var/lib/mysql-files/directors.csv'
INTO TABLE directors
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;

-- Load ActorsMovies Data
LOAD DATA INFILE '/var/lib/mysql-files/actors_movies.csv'
INTO TABLE actorsMovies
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;

-- Load DirectorsMovies Data
LOAD DATA INFILE '/var/lib/mysql-files/directors_movies.csv'
INTO TABLE directorsMovies
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;

LOAD DATA INFILE '/var/lib/mysql-files/personality_data.csv'
INTO TABLE personalityData
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;

LOAD DATA INFILE '/var/lib/mysql-files/ratings_personality.csv'
INTO TABLE ratingsPersonality
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;

-- Load Links Data
LOAD DATA INFILE '/var/lib/mysql-files/users.csv'
INTO TABLE users
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;

-- Load Links Data
LOAD DATA INFILE '/var/lib/mysql-files/lists.csv'
INTO TABLE lists
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;

-- Load Links Data
LOAD DATA INFILE '/var/lib/mysql-files/list_movies.csv'
INTO TABLE list_movies
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;
