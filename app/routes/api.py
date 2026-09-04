"""Versionless REST API for first-party clients."""
from flask import Blueprint, jsonify, request, session
from werkzeug.security import check_password_hash, generate_password_hash
from app.auth.routes import child_required, parent_required
from app.extensions import db, limiter
from app.models import Child, Completion, Parent, RewardEvent
from app.services.curriculum import LESSONS, PATHS
from app.services.rewards import complete_lesson

api_bp = Blueprint("api", __name__, url_prefix="/api")


def payload(): return request.get_json(silent=True) or {}
def error(message, status=400): return jsonify(error={"message": message}), status
def child_json(child): return {"id": child.id, "name": child.name, "age": child.age, "xp": child.xp, "points": child.points}


@api_bp.post("/auth/register")
@limiter.limit("5 per minute")
def register():
    data = payload(); name, email, password = str(data.get("name", "")).strip(), str(data.get("email", "")).strip().lower(), str(data.get("password", ""))
    if not 1 <= len(name) <= 120 or "@" not in email or len(password) < 8: return error("Invalid registration details.")
    if Parent.query.filter_by(email=email).first(): return error("Email is already registered.", 409)
    parent = Parent(name=name, email=email, password_hash=generate_password_hash(password)); db.session.add(parent); db.session.commit()
    session.clear(); session.permanent = True; session.update(parent_id=parent.id, parent_name=parent.name)
    return jsonify(data={"id": parent.id, "name": parent.name, "email": parent.email}), 201


@api_bp.post("/auth/login")
@limiter.limit("5 per minute")
def login():
    data = payload(); parent = Parent.query.filter_by(email=str(data.get("email", "")).strip().lower()).first()
    if not parent or not check_password_hash(parent.password_hash, str(data.get("password", ""))): return error("Invalid credentials.", 401)
    session.clear(); session.permanent = True; session.update(parent_id=parent.id, parent_name=parent.name)
    return jsonify(data={"id": parent.id, "name": parent.name})


@api_bp.post("/auth/logout")
def logout(): session.clear(); return "", 204


@api_bp.get("/users/me")
def me():
    parent = db.session.get(Parent, session.get("parent_id"))
    if not parent: return error("Authentication required.", 401)
    return jsonify(data={"id": parent.id, "name": parent.name, "email": parent.email})


@api_bp.get("/learners")
def learners():
    if not parent_required(): return error("Authentication required.", 401)
    return jsonify(data=[child_json(child) for child in Child.query.filter_by(parent_id=session["parent_id"]).all()])


@api_bp.post("/learners")
def create_learner():
    if not parent_required(): return error("Authentication required.", 401)
    data = payload(); name, pin = str(data.get("name", "")).strip(), str(data.get("pin", "")); age = data.get("age")
    if not 1 <= len(name) <= 80 or not isinstance(age, int) or age not in range(4, 10) or not (pin.isdigit() and len(pin) == 4): return error("Invalid learner details.")
    child = Child(parent_id=session["parent_id"], name=name, age=age, pin_hash=generate_password_hash(pin)); db.session.add(child); db.session.commit()
    return jsonify(data=child_json(child)), 201


@api_bp.get("/courses")
def courses(): return jsonify(data=list(PATHS.values()))


@api_bp.get("/courses/<int:path_id>/lessons")
def course_lessons(path_id):
    path = PATHS.get(path_id)
    if not path: return error("Course not found.", 404)
    return jsonify(data=[LESSONS[lesson_id] for lesson_id in path["lesson_ids"]])


@api_bp.get("/lessons/<int:lesson_id>")
def lesson(lesson_id):
    result = LESSONS.get(lesson_id)
    return jsonify(data=result) if result else error("Lesson not found.", 404)


@api_bp.get("/progress")
def progress():
    if not child_required(): return error("Learner authentication required.", 401)
    return jsonify(data=[item.lesson_key for item in Completion.query.filter_by(child_id=session["child_id"]).all()])


@api_bp.post("/progress/<int:lesson_id>")
def complete(lesson_id):
    if not child_required(): return error("Learner authentication required.", 401)
    lesson, child = LESSONS.get(lesson_id), db.session.get(Child, session["child_id"])
    if not lesson or not child: return error("Lesson not found.", 404)
    awarded = complete_lesson(child, lesson)
    return jsonify(data={"awarded": awarded, "xp": child.xp, "points": child.points}), 201 if awarded else 200


@api_bp.get("/rewards")
def rewards():
    if not child_required(): return error("Learner authentication required.", 401)
    events = RewardEvent.query.filter_by(child_id=session["child_id"]).order_by(RewardEvent.created_at.desc()).all()
    return jsonify(data=[{"lesson_key": e.completion_lesson_key, "xp": e.xp_delta, "points": e.points_delta, "created_at": e.created_at.isoformat()} for e in events])


@api_bp.get("/admin")
def admin():
    parent = db.session.get(Parent, session.get("parent_id")); allowed = {x.strip().lower() for x in __import__("os").environ.get("ADMIN_EMAILS", "").split(",") if x.strip()}
    if not parent or parent.email.lower() not in allowed: return error("Administrator access required.", 403)
    return jsonify(data={"parents": Parent.query.count(), "learners": Child.query.count(), "completions": Completion.query.count()})
