CREATE DATABASE IF NOT EXISTS movielens;
USE movielens;

-- Create Movies Table
CREATE TABLE IF NOT EXISTS movies (
    movieId INT PRIMARY KEY,
    title VARCHAR(255),
    genres VARCHAR(255)
);

-- Load Movies Data (with duplicate handling)
LOAD DATA INFILE '/var/lib/mysql-files/movies.csv'
IGNORE INTO TABLE movies
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"' 
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;