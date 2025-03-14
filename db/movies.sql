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
    tmdbId INT
);

-- Create Ratings Table
CREATE TABLE IF NOT EXISTS ratings (
    uniqueId INT PRIMARY KEY,
    userId INT,
    movieId INT,
    rating FLOAT,
    timestamp VARCHAR(255)
);

-- Create Tags Table
CREATE TABLE IF NOT EXISTS tags (
    uniqueId INT PRIMARY KEY,
    userId INT,
    movieId INT,
    tag VARCHAR(255),
    timestamp VARCHAR(255)
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
    movieId INT
);

-- Create DirectorsMovies Table
CREATE TABLE IF NOT EXISTS directorsMovies (
    uniqueId INT PRIMARY KEY,
    directorId VARCHAR(255),
    movieId INT
);

-- Load Movies Data
LOAD DATA INFILE '/var/lib/mysql-files/movies.csv'
INTO TABLE movies
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS
(movieId, title, genres, year, actors, directors, @runtime, language, posterUrl, @boxOffice, @imdbRating, @imdbVotes)
SET
    runtime = NULLIF(@runtime, ''),
    boxOffice = NULLIF(@boxOffice, ''),
    imdbRating = NULLIF(@imdbRating, ''),
    imdbVotes = NULLIF(@imdbVotes, '');

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
