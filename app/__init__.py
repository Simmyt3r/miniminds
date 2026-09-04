"""MiniMinds application factory."""
import os
from datetime import timedelta
from pathlib import Path
from flask import Flask
from .extensions import csrf, db, limiter, migrate


def create_app(test_config=None):
    root = Path(__file__).resolve().parents[1]
    database_url = os.environ.get("DATABASE_URL")
    if database_url:
        database_url = database_url.replace("postgres://", "postgresql+psycopg://", 1).replace("postgresql://", "postgresql+psycopg://", 1)
    else:
        database_url = f"sqlite:///{os.environ.get('SQLITE_PATH', root / 'instance' / 'miniminds.db')}"
    app = Flask(__name__, template_folder=str(root / "templates"), static_folder=str(root / "static"))
    app.config.from_mapping(
        SECRET_KEY=os.environ.get("SECRET_KEY", "development-only-change-me"),
        SQLALCHEMY_DATABASE_URI=database_url,
        SQLALCHEMY_TRACK_MODIFICATIONS=False,
        PERMANENT_SESSION_LIFETIME=timedelta(days=30),
        SESSION_COOKIE_HTTPONLY=True, SESSION_COOKIE_SAMESITE="Lax",
        SESSION_COOKIE_SECURE=os.environ.get("FLASK_ENV") == "production" or bool(os.environ.get("VERCEL")),
        WTF_CSRF_TIME_LIMIT=3600,
        RATELIMIT_STORAGE_URI="memory://",
    )
    if test_config: app.config.update(test_config)
    if app.config["SESSION_COOKIE_SECURE"] and app.config["SECRET_KEY"] == "development-only-change-me":
        raise RuntimeError("SECRET_KEY must be configured in production.")
    db.init_app(app); migrate.init_app(app, db); csrf.init_app(app); limiter.init_app(app)
    from .models import Parent  # ensure Alembic sees models
    from .auth.routes import auth_bp
    from .routes.web import web_bp
    from .routes.api import api_bp
    app.register_blueprint(auth_bp); app.register_blueprint(web_bp); app.register_blueprint(api_bp)
    csrf.exempt(api_bp)
    # Empty SQLite files are a local-development convenience; production uses migrations.
    if database_url.startswith("sqlite"):
        with app.app_context(): db.create_all()
    return app
