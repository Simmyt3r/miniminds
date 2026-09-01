import os
import sqlite3
from datetime import timedelta
from pathlib import Path
from urllib.parse import parse_qsl, urlencode, urlsplit, urlunsplit

from flask import Flask, abort, flash, g, redirect, render_template, request, session, url_for
from werkzeug.security import check_password_hash, generate_password_hash

BASE_DIR = Path(__file__).resolve().parent
# `DATABASE_URL` is the conventional name used by most hosts.  The second
# name lets this app consume the Supabase pooler URL without copying a secret
# into source control.
def postgres_url_from_environment():
    """Return a psycopg-compatible Postgres URL without host-specific hints."""
    value = os.environ.get("DATABASE_URL") or os.environ.get("mini_POSTGRES_URL", "")
    if not value:
        return ""
    parsed = urlsplit(value)
    # Supabase's dashboard URLs may include `pgbouncer` or `supa` markers.
    # They are useful to its clients but are not libpq connection parameters.
    query = [(key, item) for key, item in parse_qsl(parsed.query, keep_blank_values=True)
             if key not in {"pgbouncer", "supa"}]
    return urlunsplit((parsed.scheme, parsed.netloc, parsed.path, urlencode(query), parsed.fragment))


DATABASE_URL = postgres_url_from_environment()
# Vercel's deployed application directory is read-only.  Keep SQLite useful for
# local development while putting its serverless fallback in the writable temp
# directory.  Production deployments should still provide DATABASE_URL so data
# survives function restarts.
DEFAULT_SQLITE_PATH = Path("/tmp/miniminds.db") if os.environ.get("VERCEL") else BASE_DIR / "instance" / "miniminds.db"
LOCAL_DB = Path(os.environ.get("SQLITE_PATH", DEFAULT_SQLITE_PATH))

app = Flask(__name__, static_folder="assets")
app.config.update(
    SECRET_KEY=os.environ.get("SECRET_KEY", "development-only-change-me"),
    PERMANENT_SESSION_LIFETIME=timedelta(days=30),
    SESSION_COOKIE_HTTPONLY=True,
    SESSION_COOKIE_SAMESITE="Lax",
    SESSION_COOKIE_SECURE=os.environ.get("VERCEL") == "1",
)

COURSES = [
    ("Coding Adventures", "coding", "Learn patterns, commands, and creative problem-solving."),
    ("Business Buddies", "business", "Discover saving, choices, and the joy of building ideas."),
    ("Story Magic", "story", "Read, imagine, and solve adventures one page at a time."),
]
LESSONS = {
    "Coding Adventures": [
        ("Hello, Computer!", 10, 5), ("Colors and Commands", 15, 8),
        ("The Loop Detective", 20, 10), ("Build a Bug Bot", 18, 9),
        ("Super Sprite Challenge", 25, 12), ("Code Your Celebration", 22, 11),
    ],
    "Business Buddies": [
        ("Money Matters", 10, 5), ("The Lemonade Stand", 20, 10),
        ("Saving Secrets", 15, 8), ("The Kindness Shop", 18, 9),
        ("Plan a Picnic", 20, 10), ("Dream Big, Budget Smart", 25, 12),
    ],
    "Story Magic": [
        ("The Brave Little Robot", 10, 5), ("Dragon's Math Adventure", 15, 8),
        ("The Friendship Garden", 12, 6), ("Moonlight Map Makers", 18, 9),
        ("The Lost Library Key", 20, 10), ("Write a Happy Ending", 22, 11),
    ],
}

def db():
    if "db" not in g:
        if DATABASE_URL:
            import psycopg
            g.db = psycopg.connect(DATABASE_URL, autocommit=True)
            g.postgres = True
        else:
            LOCAL_DB.parent.mkdir(parents=True, exist_ok=True)
            g.db = sqlite3.connect(LOCAL_DB)
            g.db.row_factory = sqlite3.Row
            g.postgres = False
    return g.db

def execute(sql, params=()):
    conn = db()
    if g.postgres:
        sql = sql.replace("?", "%s")
    cursor = conn.cursor()
    cursor.execute(sql, params)
    if not g.postgres:
        conn.commit()
    return cursor

def one(sql, params=()):
    cursor = execute(sql, params)
    row = cursor.fetchone()
    if row is None:
        return None
    if isinstance(row, sqlite3.Row):
        return dict(row)
    return dict(zip([column.name for column in cursor.description], row))

def rows(sql, params=()):
    cursor = execute(sql, params)
    result = cursor.fetchall()
    if not result:
        return []
    if isinstance(result[0], sqlite3.Row):
        return [dict(item) for item in result]
    columns = [column.name for column in cursor.description]
    return [dict(zip(columns, item)) for item in result]

def init_db():
    if DATABASE_URL:
        statements = [
            "CREATE TABLE IF NOT EXISTS parents (id BIGSERIAL PRIMARY KEY, name TEXT NOT NULL CHECK (char_length(name) BETWEEN 1 AND 120), email TEXT NOT NULL, password_hash TEXT NOT NULL CHECK (char_length(password_hash) > 0), created_at TIMESTAMPTZ NOT NULL DEFAULT now(), updated_at TIMESTAMPTZ NOT NULL DEFAULT now())",
            "CREATE UNIQUE INDEX IF NOT EXISTS parents_email_unique ON parents (lower(email))",
            "CREATE TABLE IF NOT EXISTS children (id BIGSERIAL PRIMARY KEY, parent_id BIGINT NOT NULL REFERENCES parents(id) ON DELETE CASCADE, name TEXT NOT NULL CHECK (char_length(name) BETWEEN 1 AND 80), age SMALLINT NOT NULL CHECK (age BETWEEN 4 AND 9), pin_hash TEXT NOT NULL CHECK (char_length(pin_hash) > 0), points INTEGER NOT NULL DEFAULT 0 CHECK (points >= 0), xp INTEGER NOT NULL DEFAULT 0 CHECK (xp >= 0), created_at TIMESTAMPTZ NOT NULL DEFAULT now(), updated_at TIMESTAMPTZ NOT NULL DEFAULT now())",
            "CREATE INDEX IF NOT EXISTS children_parent_id_idx ON children (parent_id, id DESC)",
            "CREATE TABLE IF NOT EXISTS completions (child_id BIGINT NOT NULL REFERENCES children(id) ON DELETE CASCADE, lesson_key TEXT NOT NULL CHECK (char_length(lesson_key) BETWEEN 1 AND 200), completed_at TIMESTAMPTZ NOT NULL DEFAULT now(), PRIMARY KEY(child_id, lesson_key))",
            "CREATE INDEX IF NOT EXISTS completions_child_completed_idx ON completions (child_id, completed_at DESC)",
            "CREATE TABLE IF NOT EXISTS reward_events (id BIGSERIAL PRIMARY KEY, child_id BIGINT NOT NULL REFERENCES children(id) ON DELETE CASCADE, completion_lesson_key TEXT NOT NULL, xp_delta INTEGER NOT NULL CHECK (xp_delta >= 0), points_delta INTEGER NOT NULL CHECK (points_delta >= 0), created_at TIMESTAMPTZ NOT NULL DEFAULT now(), UNIQUE(child_id, completion_lesson_key))",
            "CREATE INDEX IF NOT EXISTS reward_events_child_created_idx ON reward_events (child_id, created_at DESC)",
        ]
    else:
        statements = [
            "CREATE TABLE IF NOT EXISTS parents (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, email TEXT UNIQUE NOT NULL, password_hash TEXT NOT NULL)",
            "CREATE TABLE IF NOT EXISTS children (id INTEGER PRIMARY KEY AUTOINCREMENT, parent_id INTEGER NOT NULL, name TEXT NOT NULL, age INTEGER NOT NULL CHECK(age BETWEEN 4 AND 9), pin_hash TEXT NOT NULL, points INTEGER NOT NULL DEFAULT 0, xp INTEGER NOT NULL DEFAULT 0)",
            "CREATE TABLE IF NOT EXISTS completions (child_id INTEGER NOT NULL, lesson_key TEXT NOT NULL, PRIMARY KEY(child_id, lesson_key))",
        ]
    for statement in statements:
        execute(statement)

@app.before_request
def prepare_database():
    init_db()

@app.teardown_appcontext
def close_db(_error):
    conn = g.pop("db", None)
    if conn is not None:
        conn.close()

def parent_required():
    if "parent_id" not in session:
        flash("Please sign in as a parent first.", "error")
        return False
    return True

def child_required():
    if "child_id" not in session:
        flash("Choose a learner and enter their PIN first.", "error")
        return False
    return True

@app.route("/")
def home():
    return render_template("home.html", courses=COURSES)

@app.route("/register", methods=["GET", "POST"])
def register():
    if request.method == "POST":
        name, email, password = (request.form.get(key, "").strip() for key in ("name", "email", "password"))
        if not name or "@" not in email or len(password) < 8:
            flash("Enter your name, a valid email, and a password with at least 8 characters.", "error")
        elif one("SELECT id FROM parents WHERE email = ?", (email.lower(),)):
            flash("That email already has an account. Try signing in.", "error")
        else:
            cursor = execute("INSERT INTO parents (name, email, password_hash) VALUES (?, ?, ?)", (name, email.lower(), generate_password_hash(password)))
            parent_id = cursor.lastrowid if not g.postgres else one("SELECT id FROM parents WHERE email = ?", (email.lower(),))["id"]
            session.clear(); session.permanent = True; session["parent_id"] = parent_id; session["parent_name"] = name
            return redirect(url_for("parent_dashboard"))
    return render_template("auth.html", mode="register")

@app.route("/login", methods=["GET", "POST"])
def login():
    if request.method == "POST":
        user = one("SELECT id, name, password_hash FROM parents WHERE email = ?", (request.form.get("email", "").strip().lower(),))
        if user and check_password_hash(user["password_hash"], request.form.get("password", "")):
            session.clear(); session.permanent = True
            session["parent_id"] = user["id"]
            session["parent_name"] = user["name"]
            return redirect(url_for("parent_dashboard"))
        flash("We couldn't match that email and password.", "error")
    return render_template("auth.html", mode="login")

@app.route("/logout")
def logout():
    session.clear(); flash("You have been signed out.", "success")
    return redirect(url_for("home"))

@app.route("/parent", methods=["GET", "POST"])
def parent_dashboard():
    if not parent_required(): return redirect(url_for("login"))
    if request.method == "POST":
        name = request.form.get("name", "").strip(); age = request.form.get("age", type=int); pin = request.form.get("pin", "")
        if not name or not age or not 4 <= age <= 9 or not (pin.isdigit() and len(pin) == 4):
            flash("Add a name, an age from 4–9, and a four-digit PIN.", "error")
        else:
            execute("INSERT INTO children (parent_id, name, age, pin_hash) VALUES (?, ?, ?, ?)", (session["parent_id"], name, age, generate_password_hash(pin)))
            flash(f"{name}'s profile is ready!", "success")
            return redirect(url_for("parent_dashboard"))
    children = rows("SELECT id, name, age, points, xp FROM children WHERE parent_id = ? ORDER BY id DESC", (session["parent_id"],))
    return render_template("parent.html", children=children)

@app.route("/kids", methods=["GET", "POST"])
def kids_login():
    if request.method == "POST":
        child = one("SELECT id, name, pin_hash FROM children WHERE id = ?", (request.form.get("child_id", type=int),))
        if child and check_password_hash(child["pin_hash"], request.form.get("pin", "")):
            session.pop("child_id", None); session["child_id"] = child["id"]
            session["child_name"] = child["name"]
            return redirect(url_for("kid_dashboard"))
        flash("That PIN doesn't match. Ask your grown-up for help.", "error")
    children = rows("SELECT id, name, age FROM children ORDER BY name")
    return render_template("kids_login.html", children=children)

@app.route("/learn")
def kid_dashboard():
    if not child_required(): return redirect(url_for("kids_login"))
    child = one("SELECT name, points, xp FROM children WHERE id = ?", (session["child_id"],))
    completed = {r["lesson_key"] for r in rows("SELECT lesson_key FROM completions WHERE child_id = ?", (session["child_id"],))}
    return render_template("learn.html", child=child, courses=COURSES, lessons=LESSONS, completed=completed)

@app.post("/complete/<course>/<int:index>")
def complete_lesson(course, index):
    if not child_required(): return redirect(url_for("kids_login"))
    lessons = LESSONS.get(course)
    if not lessons or index < 0 or index >= len(lessons): abort(404)
    key = f"{course}:{index}"
    # The unique completion key, not a read-then-write check, makes this
    # idempotent when a learner double-clicks or retries a request.
    if g.postgres:
        inserted = one(
            "INSERT INTO completions (child_id, lesson_key) VALUES (?, ?) ON CONFLICT DO NOTHING RETURNING child_id",
            (session["child_id"], key),
        )
    else:
        inserted = execute(
            "INSERT OR IGNORE INTO completions (child_id, lesson_key) VALUES (?, ?)",
            (session["child_id"], key),
        ).rowcount
    if inserted:
        title, xp, points = lessons[index]
        execute("UPDATE children SET xp = xp + ?, points = points + ? WHERE id = ?", (xp, points, session["child_id"]))
        if g.postgres:
            execute(
                "INSERT INTO reward_events (child_id, completion_lesson_key, xp_delta, points_delta) VALUES (?, ?, ?, ?)",
                (session["child_id"], key, xp, points),
            )
        flash(f"Amazing work! You earned {xp} XP and {points} stars for {title}.", "success")
    return redirect(url_for("kid_dashboard"))

if __name__ == "__main__":
    app.run(debug=True)
