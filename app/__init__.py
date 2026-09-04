"""MiniMinds application factory."""
import os
import secrets
from datetime import timedelta
from pathlib import Path
from tempfile import gettempdir
from flask import Flask
from .extensions import csrf, db, limiter, migrate


def create_app(test_config=None):
    root = Path(__file__).resolve().parents[1]
    is_serverless = bool(os.environ.get("VERCEL"))
    database_url = os.environ.get("DATABASE_URL")
    if database_url:
        database_url = database_url.replace("postgres://", "postgresql+psycopg://", 1).replace("postgresql://", "postgresql+psycopg://", 1)
    else:
        # Vercel only permits writes in /tmp. This keeps the public site available
        # when its database environment variable has not been configured yet.
        sqlite_path = os.environ.get("SQLITE_PATH") or (
            Path(gettempdir()) / "miniminds.db" if is_serverless else root / "instance" / "miniminds.db"
        )
        database_url = f"sqlite:///{sqlite_path}"
    secret_key = os.environ.get("SECRET_KEY")
    if not secret_key and is_serverless:
        # A process-local fallback prevents import-time function failures. It is
        # deliberately not persisted: production deployments should configure a
        # stable SECRET_KEY so signed sessions survive cold starts.
        secret_key = secrets.token_urlsafe(48)
    app = Flask(__name__, template_folder=str(root / "templates"), static_folder=str(root / "static"))
    app.config.from_mapping(
        SECRET_KEY=secret_key or "development-only-change-me",
        SQLALCHEMY_DATABASE_URI=database_url,
        SQLALCHEMY_TRACK_MODIFICATIONS=False,
        PERMANENT_SESSION_LIFETIME=timedelta(days=30),
        SESSION_COOKIE_HTTPONLY=True, SESSION_COOKIE_SAMESITE="Lax",
        SESSION_COOKIE_SECURE=os.environ.get("FLASK_ENV") == "production" or bool(os.environ.get("VERCEL")),
        WTF_CSRF_TIME_LIMIT=3600,
        RATELIMIT_STORAGE_URI="memory://",
    )
    if test_config: app.config.update(test_config)
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
