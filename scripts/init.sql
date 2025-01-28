-- Create Database
CREATE DATABASE IF NOT EXISTS movielens;
USE movielens;

-- Create Movies Table
CREATE TABLE IF NOT EXISTS movies (
    movieId INT PRIMARY KEY,
    title VARCHAR(255),
    genres VARCHAR(255)
);

-- Create Links Table
CREATE TABLE IF NOT EXISTS links (
    movieId INT PRIMARY KEY,
    imdbId INT,
    tmdbId INT
);

-- Create Ratings Table
CREATE TABLE IF NOT EXISTS ratings (
    userId INT,
    movieId INT,
    rating FLOAT,
    timestamp BIGINT
);

-- Create Tags Table
CREATE TABLE IF NOT EXISTS tags (
    userId INT,
    movieId INT,
    tag VARCHAR(255),
    timestamp BIGINT
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