import os
import tempfile
import unittest

fd, path = tempfile.mkstemp()
os.close(fd)
os.environ["SQLITE_PATH"] = path
os.environ["SECRET_KEY"] = "test-secret"
import app

class MiniMindsTests(unittest.TestCase):
    def setUp(self):
        self.client = app.app.test_client()
        with app.app.app_context():
            app.init_db()
            app.execute("DELETE FROM completions")
            app.execute("DELETE FROM children")
            app.execute("DELETE FROM parents")

    def test_parent_login_persists_and_can_add_child(self):
        response = self.client.post('/register', data={'name':'Ava Parent','email':'ava@example.com','password':'password1'}, follow_redirects=True)
        self.assertIn(b'Add a learner', response.data)
        response = self.client.post('/parent', data={'name':'Milo','age':'6','pin':'1234'}, follow_redirects=True)
        self.assertIn(b'Milo', response.data)
        with app.app.app_context():
            child_id = app.one("SELECT id FROM children WHERE name = ?", ('Milo',))['id']
        response = self.client.post('/kids', data={'child_id': str(child_id),'pin':'1234'}, follow_redirects=True)
        self.assertIn(b"Let's make today amazing", response.data)

    def test_assets_are_served_by_flask(self):
        response = self.client.get('/assets/css/style.css')

        self.assertEqual(response.status_code, 200)
        self.assertIn(b'body', response.data)

    def test_supabase_pooler_url_removes_non_libpq_parameters(self):
        previous = os.environ.get('DATABASE_URL')
        os.environ['DATABASE_URL'] = 'postgres://user:pass@host:6543/db?sslmode=require&pgbouncer=true&supa=pooler'
        try:
            self.assertEqual(
                app.postgres_url_from_environment(),
                'postgres://user:pass@host:6543/db?sslmode=require',
            )
        finally:
            if previous is None:
                os.environ.pop('DATABASE_URL', None)
            else:
                os.environ['DATABASE_URL'] = previous

    def test_learning_dashboard_shows_the_expanded_lesson_library(self):
        response = self.client.get('/')
        self.assertIn(b'6 playful lessons', response.data)
        self.assertEqual(len(app.LESSONS['Coding Adventures']), 6)
        self.assertEqual(len(app.LESSONS['Business Buddies']), 6)
        self.assertEqual(len(app.LESSONS['Story Magic']), 6)

    def test_completing_a_lesson_twice_only_awards_once(self):
        self.client.post('/register', data={'name':'Reward Parent','email':'rewards@example.com','password':'password1'})
        self.client.post('/parent', data={'name':'Ivy','age':'7','pin':'9876'})
        with app.app.app_context():
            child_id = app.one("SELECT id FROM children WHERE name = ?", ('Ivy',))['id']
        self.client.post('/kids', data={'child_id': str(child_id), 'pin':'9876'})

        self.client.post('/complete/Coding%20Adventures/0')
        self.client.post('/complete/Coding%20Adventures/0')

        with app.app.app_context():
            child = app.one("SELECT xp, points FROM children WHERE id = ?", (child_id,))
        self.assertEqual((child['xp'], child['points']), (10, 5))

if __name__ == '__main__':
    unittest.main()
