"""Database models for MiniMinds."""
from datetime import datetime, timezone
from app.extensions import db


def utcnow():
    return datetime.now(timezone.utc)


class TimestampMixin:
    created_at = db.Column(db.DateTime(timezone=True), nullable=False, default=utcnow)
    updated_at = db.Column(db.DateTime(timezone=True), nullable=False, default=utcnow, onupdate=utcnow)


class Parent(TimestampMixin, db.Model):
    __tablename__ = "parents"
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(120), nullable=False)
    email = db.Column(db.String(255), nullable=False, unique=True, index=True)
    password_hash = db.Column(db.String(255), nullable=False)
    children = db.relationship("Child", back_populates="parent", cascade="all, delete-orphan")


class Child(TimestampMixin, db.Model):
    __tablename__ = "children"
    id = db.Column(db.Integer, primary_key=True)
    parent_id = db.Column(db.Integer, db.ForeignKey("parents.id", ondelete="CASCADE"), nullable=False, index=True)
    name = db.Column(db.String(80), nullable=False)
    age = db.Column(db.SmallInteger, nullable=False)
    pin_hash = db.Column(db.String(255), nullable=False)
    points = db.Column(db.Integer, nullable=False, default=0)
    xp = db.Column(db.Integer, nullable=False, default=0)
    parent = db.relationship("Parent", back_populates="children")
    completions = db.relationship("Completion", back_populates="child", cascade="all, delete-orphan")


class Completion(db.Model):
    __tablename__ = "completions"
    child_id = db.Column(db.Integer, db.ForeignKey("children.id", ondelete="CASCADE"), primary_key=True)
    lesson_key = db.Column(db.String(200), primary_key=True)
    completed_at = db.Column(db.DateTime(timezone=True), nullable=False, default=utcnow, index=True)
    child = db.relationship("Child", back_populates="completions")


class RewardEvent(db.Model):
    __tablename__ = "reward_events"
    __table_args__ = (db.UniqueConstraint("child_id", "completion_lesson_key", name="uq_reward_lesson"),)
    id = db.Column(db.Integer, primary_key=True)
    child_id = db.Column(db.Integer, db.ForeignKey("children.id", ondelete="CASCADE"), nullable=False, index=True)
    completion_lesson_key = db.Column(db.String(200), nullable=False)
    xp_delta = db.Column(db.Integer, nullable=False)
    points_delta = db.Column(db.Integer, nullable=False)
    created_at = db.Column(db.DateTime(timezone=True), nullable=False, default=utcnow)
