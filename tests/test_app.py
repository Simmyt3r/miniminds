import os
import tempfile
import unittest

fd, path = tempfile.mkstemp(); os.close(fd)
os.environ["SQLITE_PATH"] = path
os.environ["SECRET_KEY"] = "test-secret"
from app import create_app
from app.extensions import db
from app.models import Child, Parent, RewardEvent


class MiniMindsTests(unittest.TestCase):
    def setUp(self):
        self.app = create_app({"TESTING": True, "WTF_CSRF_ENABLED": False, "SQLALCHEMY_DATABASE_URI": f"sqlite:///{path}"})
        self.client = self.app.test_client()
        with self.app.app_context():
            db.drop_all(); db.create_all()

    def register_parent_and_child(self):
        self.client.post("/register", data={"name": "Ava Parent", "email": "ava@example.com", "password": "password1"})
        self.client.post("/parent", data={"name": "Milo", "age": "6", "pin": "1234"})
        with self.app.app_context(): return Child.query.filter_by(name="Milo").first().id

    def test_parent_authentication_and_child_profile(self):
        child_id = self.register_parent_and_child()
        self.assertIsNotNone(child_id)
        response = self.client.post("/kids", data={"child_id": child_id, "pin": "1234"}, follow_redirects=True)
        self.assertIn(b"Let's make today amazing", response.data)

    def test_models_have_timestamps_and_indexes(self):
        self.register_parent_and_child()
        with self.app.app_context():
            parent = Parent.query.one(); child = Child.query.one()
            self.assertIsNotNone(parent.created_at); self.assertIsNotNone(child.updated_at)
            self.assertTrue(any(index.name == "ix_children_parent_id" for index in Child.__table__.indexes))

    def test_api_courses_and_learner_creation(self):
        self.client.post("/api/auth/register", json={"name": "API Parent", "email": "api@example.com", "password": "password1"})
        created = self.client.post("/api/learners", json={"name": "Ivy", "age": 7, "pin": "7777"})
        self.assertEqual(created.status_code, 201)
        self.assertEqual(self.client.get("/api/courses").get_json()["data"].__len__(), 25)
        self.assertEqual(self.client.get("/api/lessons/1000").status_code, 200)

    def test_progress_awards_rewards_once(self):
        child_id = self.register_parent_and_child()
        self.client.post("/kids", data={"child_id": child_id, "pin": "1234"})
        self.assertEqual(self.client.post("/api/progress/1").status_code, 201)
        self.assertEqual(self.client.post("/api/progress/1").get_json()["data"]["awarded"], False)
        with self.app.app_context():
            child = db.session.get(Child, child_id)
            self.assertEqual((child.xp, child.points), (16, 9))
            self.assertEqual(RewardEvent.query.count(), 1)

    def test_csrf_rejects_form_posts_outside_testing(self):
        secure = create_app({"TESTING": True, "SQLALCHEMY_DATABASE_URI": f"sqlite:///{path}"})
        self.assertEqual(secure.test_client().post("/register", data={}).status_code, 400)


if __name__ == "__main__": unittest.main()
