# Production migration procedure

Provision PostgreSQL/Supabase with `database/supabase_schema.sql`, then use
Flask-Migrate (`flask --app app db upgrade`) for application schema changes.
SQLite is supported only for local development and automated tests.
