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

    def test_parent_login_persists_and_can_add_child(self):
        response = self.client.post('/register', data={'name':'Ava Parent','email':'ava@example.com','password':'password1'}, follow_redirects=True)
        self.assertIn(b'Add a learner', response.data)
        response = self.client.post('/parent', data={'name':'Milo','age':'6','pin':'1234'}, follow_redirects=True)
        self.assertIn(b'Milo', response.data)
        response = self.client.post('/kids', data={'child_id':'1','pin':'1234'}, follow_redirects=True)
        self.assertIn(b"Let's make today amazing", response.data)

    def test_assets_are_served_by_flask(self):
        response = self.client.get('/assets/css/style.css')

        self.assertEqual(response.status_code, 200)
        self.assertIn(b'body', response.data)

if __name__ == '__main__':
    unittest.main()
