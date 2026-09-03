"""Initial MiniMinds SQLAlchemy schema.

Revision ID: 20260903_0001
Revises:
Create Date: 2026-09-03
"""
from alembic import op
import sqlalchemy as sa

revision = "20260903_0001"
down_revision = None
branch_labels = None
depends_on = None


def upgrade():
    op.create_table("parents", sa.Column("id", sa.Integer(), primary_key=True), sa.Column("name", sa.String(120), nullable=False), sa.Column("email", sa.String(255), nullable=False), sa.Column("password_hash", sa.String(255), nullable=False), sa.Column("created_at", sa.DateTime(timezone=True), nullable=False), sa.Column("updated_at", sa.DateTime(timezone=True), nullable=False))
    op.create_index("ix_parents_email", "parents", ["email"], unique=True)
    op.create_table("children", sa.Column("id", sa.Integer(), primary_key=True), sa.Column("parent_id", sa.Integer(), sa.ForeignKey("parents.id", ondelete="CASCADE"), nullable=False), sa.Column("name", sa.String(80), nullable=False), sa.Column("age", sa.SmallInteger(), nullable=False), sa.Column("pin_hash", sa.String(255), nullable=False), sa.Column("points", sa.Integer(), nullable=False, server_default="0"), sa.Column("xp", sa.Integer(), nullable=False, server_default="0"), sa.Column("created_at", sa.DateTime(timezone=True), nullable=False), sa.Column("updated_at", sa.DateTime(timezone=True), nullable=False))
    op.create_index("ix_children_parent_id", "children", ["parent_id"])
    op.create_table("completions", sa.Column("child_id", sa.Integer(), sa.ForeignKey("children.id", ondelete="CASCADE"), primary_key=True), sa.Column("lesson_key", sa.String(200), primary_key=True), sa.Column("completed_at", sa.DateTime(timezone=True), nullable=False))
    op.create_index("ix_completions_completed_at", "completions", ["completed_at"])
    op.create_table("reward_events", sa.Column("id", sa.Integer(), primary_key=True), sa.Column("child_id", sa.Integer(), sa.ForeignKey("children.id", ondelete="CASCADE"), nullable=False), sa.Column("completion_lesson_key", sa.String(200), nullable=False), sa.Column("xp_delta", sa.Integer(), nullable=False), sa.Column("points_delta", sa.Integer(), nullable=False), sa.Column("created_at", sa.DateTime(timezone=True), nullable=False), sa.UniqueConstraint("child_id", "completion_lesson_key", name="uq_reward_lesson"))
    op.create_index("ix_reward_events_child_id", "reward_events", ["child_id"])


def downgrade():
    op.drop_table("reward_events"); op.drop_table("completions"); op.drop_table("children"); op.drop_table("parents")
