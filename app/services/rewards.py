"""Idempotent XP and stars awarding."""
from sqlalchemy.exc import IntegrityError
from app.extensions import db
from app.models import Completion, RewardEvent


def complete_lesson(child, lesson):
    """Award a completion once, including when a client retries a request."""
    key = f"lesson:{lesson['id']}"
    if db.session.get(Completion, (child.id, key)):
        return False
    db.session.add(Completion(child_id=child.id, lesson_key=key))
    child.xp += lesson["xp"]
    child.points += lesson["points"]
    db.session.add(RewardEvent(child_id=child.id, completion_lesson_key=key, xp_delta=lesson["xp"], points_delta=lesson["points"]))
    try:
        db.session.commit()
    except IntegrityError:
        # The composite completion key and reward-ledger unique key make a race safe.
        db.session.rollback()
        db.session.refresh(child)
        return False
    return True
