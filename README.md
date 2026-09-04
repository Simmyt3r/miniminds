# MiniMinds Academy

MiniMinds is a Flask education platform for children aged 4–9. Parents register and manage learner profiles; learners unlock the 1,000-lesson curriculum with a PIN, track progress, and earn XP and stars. The active application is Flask. The previous PHP implementation is retained under `legacy_php/` strictly as a migration reference and is not served or deployed.

## Architecture

```text
Browser / REST client
        │
        ▼
Flask application factory (app/__init__.py)
 ├── auth/                 parent and learner session flows
 ├── routes/web.py         server-rendered education experience
 ├── routes/api.py         JSON REST API
 ├── models/               SQLAlchemy persistence models
 ├── services/             curriculum and idempotent rewards
 └── extensions.py         database, migrations, CSRF, rate limiting
        │
        ├── PostgreSQL / Supabase (production)
        └── SQLite (local development and tests only)
```

## Features

- Parent registration, password-hashed login, and protected dashboard.
- Parent-owned child learner profiles with four-digit PIN access.
- 25 paths and 1,000 lessons, completion tracking, XP, stars, and immutable reward events.
- REST resources under `/api/auth`, `/api/users`, `/api/learners`, `/api/courses`, `/api/lessons`, `/api/progress`, `/api/rewards`, and `/api/admin`.
- CSRF protection for browser forms, secure signed sessions, input validation, parameterized ORM queries, login rate limiting, and environment-gated administrator metrics.

## Local installation

```bash
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
flask --app app run --debug
```

For local use, omit `DATABASE_URL`; MiniMinds creates `instance/miniminds.db`. Never use SQLite on Vercel or for persistent production data.

## Environment variables

| Variable | Required | Purpose |
| --- | --- | --- |
| `SECRET_KEY` | Yes in production | Long random key used to sign sessions. |
| `DATABASE_URL` | Yes in production | Server-side PostgreSQL/Supabase connection URL. |
| `FLASK_ENV` | Recommended | Set to `production` to require secure session cookies. |
| `ADMIN_EMAILS` | Optional | Comma-separated parent emails allowed to call `/api/admin`. |
| `SQLITE_PATH` | Local only | Overrides the development SQLite file. |

Do not commit `.env`, database passwords, Supabase service-role keys, or browser-exposed credentials.

## Database and migrations

The SQLAlchemy models include created/updated timestamps and indexed foreign keys. For a new Supabase database, apply [`database/supabase_schema.sql`](database/supabase_schema.sql), then manage subsequent revisions with Flask-Migrate:

```bash
flask --app app db migrate -m "describe the schema change"
flask --app app db upgrade
```

See [`migrations/README.md`](migrations/README.md) for the migration policy. The app initializes an empty local database for development convenience; production deployment should run reviewed migrations before serving traffic.

## Testing and developer guide

```bash
python -m unittest discover -s tests -v
python -m compileall -q app api tests
```

Tests cover parent authentication, model timestamps/indexes, API resources, CSRF enforcement, and idempotent progress/reward awarding. Keep HTTP handlers thin, put business rules in `app/services`, and add a test whenever an authorization or reward rule changes.

## Vercel deployment

1. Create a PostgreSQL/Supabase database and apply the production schema/migrations.
2. Add `DATABASE_URL` and a generated `SECRET_KEY` in Vercel project environment variables. Use a server-side pooler URL only.
3. Set `FLASK_ENV=production`; optionally configure `ADMIN_EMAILS`.
4. Deploy. [`vercel.json`](vercel.json) routes requests to [`api/index.py`](api/index.py), which creates the Flask app. If either variable is temporarily absent, the function starts with a process-local session key and `/tmp` SQLite database instead of crashing; that fallback is ephemeral and is only intended to keep the site reachable while the production variables are configured.

Verify `/`, `/api/courses`, and a database-backed registration flow after deployment. Do not deploy without `DATABASE_URL`: serverless local files are ephemeral.
