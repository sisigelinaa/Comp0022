# Comp0022
# Movie Recommendation System

## Overview
This project is a **Movie Recommendation System** that integrates **data preprocessing, reports, rating predictions**, and a web dashboard. It uses **MySQL, Flask (Python), and PHP** to provide insights into movie genres and predictions for user ratings.

## Features
- **Dashboard**: Search movies by title, genre, actors, or IMDb rating.
- **Reports**: Genre-based statistics including popularity and polarization.
- **Analysis**: Audience pattern analysis.
- **Prediction**: Hybrid recommendation model using **Content-Based Filtering** and **Collaborative Filtering (SVD-based)**.
- **User Accounts**: Manage user preferences and ratings.

## Prerequisites
Ensure you have the following installed:
- **Docker** & **Docker Compose**
- **Git**

## Project Setup
### 1️⃣ Clone the Repository
```sh
 git clone https://github.com/yourusername/movie-recommendation.git
 cd movie-recommendation
```

### 2️⃣ Build and Start the Docker Containers
Run the following command to start all services:
```sh
 docker-compose up --build
```
This will:
- Set up a **MySQL database** with movie data.
- Run a **Flask API** for rating predictions.
- Deploy a **PHP web interface** for interaction.

### 3️⃣ Access the Web Application
Once the containers are running, open your browser and go to:
```sh
 http://localhost:8080
```

## Folder Structure
```
COMP0022/
│── backend/                   # Backend services
│   ├── predict_rating.py       # Flask API for rating predictions
│   ├── Dockerfile.python       # Python backend Dockerfile
│   ├── requirements.txt        # Python dependencies
│
│── csv/                        # Processed and normalized CSV files
│
│── db/
│   ├── movies.sql              # SQL schema for MySQL database
│
│── php/                        # Frontend application
│   ├── scripts/                # Contains PHP scripts
│   ├── Task 1 - Dashboard/     # Movie search dashboard
│   ├── Task 2 - Reports/       # Genre reports and histograms
│   ├── Task 4 - Prediction/    # Rating prediction interface
│   ├── Task 6 - Accounts/      # User authentication
│   ├── index.php               # Main entry point
│   ├── styles.css              # Styling
│
│── preprocessing/              # Data normalization scripts
│
│── src/scripts/                # Additional scripts
│
│── docker-compose.yml          # Docker configuration file
│── README.md                   # Project documentation
```

## Database Setup
- The **movies.sql** file initializes the MySQL database.
- Data is imported from **csv/** during container startup.

## API Endpoints
| Endpoint            | Method | Description |
|--------------------|--------|-------------|
| `/predict`         | POST   | Predicts user ratings using Content-Based & Collaborative Filtering |

## Stopping the Project
To stop the running containers:
```sh
 docker-compose down
```
