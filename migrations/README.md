# Database migrations

MiniMinds uses Flask-Migrate/Alembic. Create a revision after model changes with
`flask --app app db migrate -m "describe change"`, review it, then apply it with
`flask --app app db upgrade`. `db.create_all()` is retained only to make an empty
local development database usable; production deployments must run `db upgrade`.
