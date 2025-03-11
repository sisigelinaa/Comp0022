-- Create Database
CREATE DATABASE IF NOT EXISTS movielens;
USE movielens;

-- Create Movies Table
CREATE TABLE IF NOT EXISTS movies (
    movieId VARCHAR(255) PRIMARY KEY,
    title VARCHAR(255),
    genres VARCHAR(255),
    year INT,
    actors VARCHAR(255),
    directors VARCHAR(255),
    runtime INT,
    language VARCHAR(255),
    posterUrl VARCHAR(255),
    boxOffice VARCHAR(255),
    imdbRating FLOAT,
    imdbVotes INT,
    awards VARCHAR(255)
);

-- Create Links Table
CREATE TABLE IF NOT EXISTS links (
    movieId VARCHAR(255) PRIMARY KEY,
    imdbId VARCHAR(255),
    tmdbId VARCHAR(255)
);

-- Create Ratings Table
CREATE TABLE IF NOT EXISTS ratings (
    uniqueId VARCHAR(255) PRIMARY KEY,
    userId VARCHAR(255),
    movieId VARCHAR(255),
    rating FLOAT,
    timestamp BIGINT
);

-- Create Tags Table
CREATE TABLE IF NOT EXISTS tags (
    uniqueId VARCHAR(255) PRIMARY KEY,
    userId VARCHAR(255),
    movieId VARCHAR(255),
    tag VARCHAR(255),
    timestamp BIGINT
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
    uniqueId VARCHAR(255) PRIMARY KEY,
    actorId VARCHAR(255),
    movieId VARCHAR(255)
);

-- Create DirectorsMovies Table
CREATE TABLE IF NOT EXISTS directorsMovies (
    uniqueId VARCHAR(255) PRIMARY KEY,
    directorId VARCHAR(255),
    movieId VARCHAR(255)
);

-- Load Movies Data
LOAD DATA INFILE '/var/lib/mysql-files/movies.csv'
INTO TABLE movies
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;

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
