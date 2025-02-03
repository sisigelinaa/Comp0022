-- Create Database
CREATE DATABASE IF NOT EXISTS movielens;
USE movielens;

-- Create Movies Table
CREATE TABLE IF NOT EXISTS movies (
    movieId VARCHAR(255) PRIMARY KEY,
    title VARCHAR(255),
    genres VARCHAR(255),
    year INT
);

-- Create Links Table
CREATE TABLE IF NOT EXISTS links (
    movieId VARCHAR(255) PRIMARY KEY,
    imdbId VARCHAR(255),
    tmdbId VARCHAR(255)
);

-- Create Ratings Table
CREATE TABLE IF NOT EXISTS ratings (
    userId VARCHAR(255),
    movieId VARCHAR(255),
    rating FLOAT,
    timestamp BIGINT
);

-- Create Tags Table
CREATE TABLE IF NOT EXISTS tags (
    userId VARCHAR(255),
    movieId VARCHAR(255),
    tag VARCHAR(255),
    timestamp BIGINT
);

-- Load Movies Data
LOAD DATA INFILE '/var/lib/mysql-files/movies_cleaned.csv'
INTO TABLE movies
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;

-- Load Links Data
LOAD DATA INFILE '/var/lib/mysql-files/links_cleaned.csv'
INTO TABLE links
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;

-- Load Ratings Data
LOAD DATA INFILE '/var/lib/mysql-files/ratings_cleaned.csv'
INTO TABLE ratings
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;

-- Load Tags Data
LOAD DATA INFILE '/var/lib/mysql-files/tags_cleaned.csv'
INTO TABLE tags
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;