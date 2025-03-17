from flask import Flask
from flask_cors import CORS

# Initialize Flask app
app = Flask(__name__)

# Enable CORS if required
CORS(app)

# Define a simple root route
@app.route("/")
def home():
    return {"message": "Backend API is running!"}
