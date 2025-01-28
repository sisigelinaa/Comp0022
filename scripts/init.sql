CREATE DATABASE IF NOT EXISTS movielens;
USE movielens;

CREATE TABLE IF NOT EXISTS movies (
    movieId INT PRIMARY KEY,
    title VARCHAR(255),
    genres VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS hello (
    a INT PRIMARY KEY,
    c VARCHAR(255)
);    

INSERT INTO movies (movieId, title, genres) VALUES (1, 'Alice', 'l');

-- LOAD DATA LOCAL INFILE '${PWD}/scripts/movies.csv'
-- INTO TABLE movies
-- FIELDS TERMINATED BY ',' 
-- ENCLOSED BY '"' 
-- LINES TERMINATED BY '\n'
-- IGNORE 1 ROWS;
