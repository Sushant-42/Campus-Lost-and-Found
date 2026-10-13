import unittest
from service.app import app

class OrdersTestCase(unittest.TestCase):
    def setUp(self):
        app.config['TESTING'] = True
        self.client = app.test_client()

    def test_unauthorized_order(self):
        response = self.client.post('/orders', json={})
        self.assertEqual(response.status_code, 401)
        data = response.get_json()
        self.assertIn("unauthorized", data["type"])

    def test_validation_errors(self):
        headers = {"Authorization": "Bearer test-token"}
        response = self.client.post('/orders', json={}, headers=headers)
        self.assertEqual(response.status_code, 422)
        data = response.get_json()
        self.assertIn("errors", data)

if __name__ == "__main__":
    unittest.main()