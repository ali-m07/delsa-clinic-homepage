import pytest
from fastapi.testclient import TestClient

from app.database import init_db, engine
from app.main import app
from app.models import Department
from app.seed import seed
from sqlmodel import Session, select, text


TABLES = (
    "appointmentrequest",
    "appointmentformfield",
    "consultant",
    "department",
    "article",
    "page",
    "sitesettings",
)


@pytest.fixture(autouse=True)
def fresh_db():
    init_db()
    with Session(engine) as session:
        for table in TABLES:
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
    assert "خدمات تخصصی کلینیک" in response.text


def test_career_department(client):
    response = client.get("/دپارتمان-مشاوره-شغلی")
    assert response.status_code == 200
    assert "مشاوره شغلی" in response.text


def test_consultants(client):
    response = client.get("/مشاوران")
    assert response.status_code == 200
    assert "مریم صالحی" in response.text


def test_about(client):
    response = client.get("/درباره-ما")
    assert response.status_code == 200
    assert "درباره ما" in response.text
    assert "اینجا کنار شماییم" in response.text
    assert "delsa-about" in response.text


def test_blog(client):
    response = client.get("/blog")
    assert response.status_code == 200
    assert "وبلاگ" in response.text


def test_appointment_form(client):
    response = client.get("/فرم-نوبت-دهی")
    assert response.status_code == 200
    assert "فرم نوبت" in response.text


def test_appointment_submit(client):
    response = client.post(
        "/فرم-نوبت-دهی",
        data={
            "full_name": "تست کاربر",
            "phone": "09123456789",
            "department": "روان‌درمانی",
        },
        follow_redirects=False,
    )
    assert response.status_code == 303
    assert response.headers["location"].endswith("success=1")
