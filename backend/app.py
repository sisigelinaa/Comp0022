from flask import Flask
from flask_cors import CORS
from film_dashboard import film_dashboard_bp
from genre_reports import genre_reports_bp
from audience_patterns import audience_patterns_bp
from predictive_ratings import predictive_ratings_bp
from personality_traits import personality_traits_bp
from festival_planner import festival_planner_bp

app = Flask(__name__)
CORS(app)  # Enable CORS for frontend calls

# Register blueprints
app.register_blueprint(film_dashboard_bp)
app.register_blueprint(genre_reports_bp)
app.register_blueprint(audience_patterns_bp)
app.register_blueprint(predictive_ratings_bp)
app.register_blueprint(personality_traits_bp)
app.register_blueprint(festival_planner_bp)

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=5000, debug=True)