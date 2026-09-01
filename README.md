# MiniMinds Academy — Flask for Vercel

MiniMinds is now a Flask application deployable as a Vercel Python serverless function. It includes parent registration, learner PIN profiles, lesson progress, rewards, and a secure persistent parent session.

## Run locally

```bash
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
flask --app app run --debug
```

Without configuration, local development uses `instance/miniminds.db`. This is intentionally a local-only convenience database.

## Deploy to Vercel

1. Create a Postgres database (Vercel Postgres, Neon, or another provider).
2. In the Vercel project settings, add `DATABASE_URL` with the Postgres connection URL.
3. Add a long random `SECRET_KEY`, for example `python -c "import secrets; print(secrets.token_urlsafe(48))"`.
4. Deploy. `vercel.json` sends every route to `api/index.py`, which exposes the Flask app.

The app creates its tables on first request. Production data—including accounts, child profiles, lesson completions, and XP—is stored in Postgres, not the serverless filesystem.

## Persistent login

A successful parent login creates a signed, HTTP-only session cookie that lasts 30 days. On Vercel it is marked `Secure`; `SameSite=Lax` reduces cross-site request exposure. Set a stable `SECRET_KEY` before deploying so sessions remain valid across function invocations and deployments.

## Tests

```bash
python -m unittest discover -s tests
```
