"""Parent and learner session endpoints."""
from flask import Blueprint, flash, redirect, render_template, request, session, url_for
from werkzeug.security import check_password_hash, generate_password_hash
from app.extensions import db, limiter
from app.models import Child, Parent

auth_bp = Blueprint("auth", __name__)


def parent_required():
    return session.get("parent_id")


def child_required():
    return session.get("child_id")


@auth_bp.route("/register", methods=["GET", "POST"])
@limiter.limit("5 per minute")
def register():
    if request.method == "POST":
        name, email, password = (request.form.get(key, "").strip() for key in ("name", "email", "password"))
        if not 1 <= len(name) <= 120 or "@" not in email or len(password) < 8:
            flash("Enter your name, a valid email, and a password with at least 8 characters.", "error")
        elif Parent.query.filter_by(email=email.lower()).first():
            flash("That email already has an account. Try signing in.", "error")
        else:
            parent = Parent(name=name, email=email.lower(), password_hash=generate_password_hash(password))
            db.session.add(parent); db.session.commit()
            session.clear(); session.permanent = True; session.update(parent_id=parent.id, parent_name=parent.name)
            return redirect(url_for("web.parent_dashboard"))
    return render_template("auth.html", mode="register")


@auth_bp.route("/login", methods=["GET", "POST"])
@limiter.limit("5 per minute", methods=["POST"])
def login():
    if request.method == "POST":
        parent = Parent.query.filter_by(email=request.form.get("email", "").strip().lower()).first()
        if parent and check_password_hash(parent.password_hash, request.form.get("password", "")):
            session.clear(); session.permanent = True; session.update(parent_id=parent.id, parent_name=parent.name)
            return redirect(url_for("web.parent_dashboard"))
        flash("We couldn't match that email and password.", "error")
    return render_template("auth.html", mode="login")


@auth_bp.get("/logout")
def logout():
    session.clear(); flash("You have been signed out.", "success")
    return redirect(url_for("web.home"))


@auth_bp.route("/kids", methods=["GET", "POST"])
@limiter.limit("10 per minute", methods=["POST"])
def kids_login():
    if request.method == "POST":
        child = db.session.get(Child, request.form.get("child_id", type=int))
        if child and check_password_hash(child.pin_hash, request.form.get("pin", "")):
            session.pop("child_id", None); session.update(child_id=child.id, child_name=child.name)
            return redirect(url_for("web.kid_dashboard"))
        flash("That PIN doesn't match. Ask your grown-up for help.", "error")
    children = Child.query.order_by(Child.name).all()
    return render_template("kids_login.html", children=children)
