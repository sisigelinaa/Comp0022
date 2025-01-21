-- Create Database
CREATE DATABASE IF NOT EXISTS movielens;
USE movielens;

-- Create Movies Table
CREATE TABLE IF NOT EXISTS movies (
    movieId INT PRIMARY KEY,
    title VARCHAR(255),
    genres VARCHAR(255)
);

-- Create Movies Table
CREATE TABLE IF NOT EXISTS hello (
    a INT PRIMARY KEY,
    b VARCHAR(255),
    c VARCHAR(255)
);

INSERT INTO movies (movieId, title, genres) VALUES (1, 'Alice', 'l');
-- Load Movies Data
SET GLOBAL local_infile = 1;
LOAD DATA LOCAL INFILE 'var/lib/mysql-files/movies.csv'
INTO TABLE movies
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"' 
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;

-- Add other tables and load data similarly.
