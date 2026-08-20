import pytest
from fastapi.testclient import TestClient

from app.database import init_db, engine
from app.main import app
from app.models import Department
from app.seed import seed
from sqlmodel import Session, select, text


@pytest.fixture(autouse=True)
def fresh_db():
    init_db()
    with Session(engine) as session:
        for table in ("consultant", "department", "article"):
            session.exec(text(f"DELETE FROM {table}"))
        session.commit()
    seed()
    yield


@pytest.fixture
def client():
    return TestClient(app)


def test_health(client):
    assert client.get("/health").json() == {"ok": True}


def test_home(client):
    response = client.get("/")
    assert response.status_code == 200
    assert "کلینیک دلسا" in response.text


def test_career_department(client):
    response = client.get("/دپارتمان-مشاوره-شغلی")
    assert response.status_code == 200
    assert "مشاوره شغلی" in response.text


def test_consultants(client):
    response = client.get("/مشاوران")
    assert response.status_code == 200
    assert "سپیده آزرم" in response.text
