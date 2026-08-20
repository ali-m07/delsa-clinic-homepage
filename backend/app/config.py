import os
from functools import lru_cache

from dotenv import load_dotenv

load_dotenv()


@lru_cache
def get_settings():
    return {
        "database_url": os.getenv("DATABASE_URL", "sqlite:///./delsa.db"),
        "secret_key": os.getenv("SECRET_KEY", "dev-secret-change-me"),
        "admin_username": os.getenv("ADMIN_USERNAME", "admin"),
        "admin_password": os.getenv("ADMIN_PASSWORD", "admin"),
        "site_url": os.getenv("SITE_URL", "http://localhost:8000").rstrip("/"),
    }
