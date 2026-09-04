"""HTML experience routes."""
from flask import Blueprint, abort, flash, redirect, render_template, request, session, url_for
from werkzeug.security import generate_password_hash
from app.auth.routes import child_required, parent_required
from app.extensions import db
from app.models import Child, Completion
from app.services.curriculum import LESSONS, PATHS, path_for_lesson
from app.services.rewards import complete_lesson

web_bp = Blueprint("web", __name__)


@web_bp.get("/")
def home(): return render_template("home.html", paths=PATHS.values())


@web_bp.route("/parent", methods=["GET", "POST"])
def parent_dashboard():
    if not parent_required(): return redirect(url_for("auth.login"))
    if request.method == "POST":
        name, age, pin = request.form.get("name", "").strip(), request.form.get("age", type=int), request.form.get("pin", "")
        if not 1 <= len(name) <= 80 or age not in range(4, 10) or not (pin.isdigit() and len(pin) == 4):
            flash("Add a name, an age from 4–9, and a four-digit PIN.", "error")
        else:
            db.session.add(Child(parent_id=session["parent_id"], name=name, age=age, pin_hash=generate_password_hash(pin)))
            db.session.commit(); flash(f"{name}'s profile is ready!", "success")
            return redirect(url_for("web.parent_dashboard"))
    children = Child.query.filter_by(parent_id=session["parent_id"]).order_by(Child.id.desc()).all()
    return render_template("parent.html", children=children)


@web_bp.get("/learn")
def kid_dashboard():
    if not child_required(): return redirect(url_for("auth.kids_login"))
    child = db.session.get(Child, session["child_id"])
    if not child: session.clear(); return redirect(url_for("auth.kids_login"))
    completed = {item.lesson_key for item in child.completions}
    progress = {path_id: sum(f"lesson:{lesson_id}" in completed for lesson_id in path["lesson_ids"]) for path_id, path in PATHS.items()}
    return render_template("learn.html", child=child, paths=PATHS.values(), path_progress=progress)


@web_bp.get("/learn/path/<int:path_id>")
def learning_path(path_id):
    if not child_required(): return redirect(url_for("auth.kids_login"))
    path = PATHS.get(path_id)
    if not path: abort(404)
    completed = {row.lesson_key for row in Completion.query.filter_by(child_id=session["child_id"]).all()}
    return render_template("path.html", path=path, lessons=[LESSONS[i] for i in path["lesson_ids"]], completed=completed)


@web_bp.get("/learn/lesson/<int:lesson_id>")
def lesson_detail(lesson_id):
    if not child_required(): return redirect(url_for("auth.kids_login"))
    lesson, path = LESSONS.get(lesson_id), path_for_lesson(lesson_id)
    if not lesson or not path: abort(404)
    return render_template("lesson.html", lesson=lesson, path=path)


@web_bp.post("/complete/<int:lesson_id>")
def complete_lesson_route(lesson_id):
    if not child_required(): return redirect(url_for("auth.kids_login"))
    lesson = LESSONS.get(lesson_id); child = db.session.get(Child, session["child_id"])
    if not lesson or not child: abort(404)
    if complete_lesson(child, lesson): flash(f"Amazing work! You earned {lesson['xp']} XP and {lesson['points']} stars for {lesson['title']}.", "success")
    return redirect(url_for("web.learning_path", path_id=path_for_lesson(lesson_id)["id"]))
