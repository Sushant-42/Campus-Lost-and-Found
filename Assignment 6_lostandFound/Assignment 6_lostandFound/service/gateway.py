import time
import requests

CATALOG_URL = "http://localhost:8001"

def check_external_service():
    """Example upstream dependency check for service availability testing."""
    url = f"{CATALOG_URL}/health"
    for attempt in range(3):
        try:
            response = requests.get(url, timeout=2)
            if response.status_code == 200:
                return True
        except requests.RequestException:
            pass
        if attempt < 2:
            time.sleep(2 ** attempt)
    return False