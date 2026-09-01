# MiniMinds Academy — Flask for Vercel

MiniMinds is now a Flask application deployable as a Vercel Python serverless function. It includes parent registration, learner PIN profiles, lesson progress, rewards, and a secure persistent parent session.

## Run locally

```bash
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
flask --app app run --debug
```

Without configuration, local development uses `instance/miniminds.db`. This is intentionally a local-only convenience database. If `DATABASE_URL` is omitted on Vercel, the app instead uses `/tmp/miniminds.db` so the function can start; that temporary database is reset whenever the function instance is replaced and must not be used for production data.

## Deploy to Vercel

1. Create a Postgres database (Supabase, Vercel Postgres, Neon, or another provider).
2. In the Vercel project settings, add the Supabase **server-side** pooler URL as `DATABASE_URL` (or `mini_POSTGRES_URL`). Do not use an anon, publishable, service-role, or browser-exposed `NEXT_PUBLIC_*` key for this setting.
3. Add a long random `SECRET_KEY`, for example `python -c "import secrets; print(secrets.token_urlsafe(48))"`.
4. Deploy. `vercel.json` sends every route to `api/index.py`, which exposes the Flask app.

The app creates its tables on first request. Production data—including accounts, child profiles, lesson completions, and XP—is stored in Postgres, not the serverless filesystem.

### Supabase production schema

For a new Supabase project, apply [`database/supabase_schema.sql`](database/supabase_schema.sql) once through the Supabase SQL editor or a privileged `psql` connection. It creates constrained, indexed parent/learner/progress tables, an immutable reward ledger, and update timestamps. The application also creates its required core tables when it starts, so deployments remain backward compatible; the SQL file is the recommended auditable provisioning artifact.

Copy `.env.example` to `.env` only for local development and fill in your own values. `.env` is ignored by Git. Rotate any credential that has been pasted into a chat, issue, terminal log, or repository history.

## Persistent login

A successful parent login creates a signed, HTTP-only session cookie that lasts 30 days. On Vercel it is marked `Secure`; `SameSite=Lax` reduces cross-site request exposure. Set a stable `SECRET_KEY` before deploying so sessions remain valid across function invocations and deployments.

## Tests

```bash
python -m unittest discover -s tests
```
