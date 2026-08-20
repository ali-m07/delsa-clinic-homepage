from pathlib import Path

from fastapi import Depends, FastAPI, HTTPException, Request
from fastapi.responses import HTMLResponse
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from sqlmodel import Session, select
from starlette.middleware.sessions import SessionMiddleware

from app.admin_views import setup_admin
from app.config import get_settings
from app.database import engine, get_session, init_db
from app.models import Article, Consultant, Department

BASE_DIR = Path(__file__).resolve().parent
templates = Jinja2Templates(directory=str(BASE_DIR / "templates"))

app = FastAPI(title="Delsa Clinic", docs_url="/api/docs", redoc_url=None)
settings = get_settings()
app.add_middleware(SessionMiddleware, secret_key=settings["secret_key"])
app.mount("/static", StaticFiles(directory=str(BASE_DIR / "static")), name="static")
setup_admin(app, engine)


@app.on_event("startup")
def on_startup():
    init_db()


def site_context(session: Session, request: Request) -> dict:
    departments = session.exec(
        select(Department).where(Department.published == True).order_by(Department.sort_order)
    ).all()
    return {
        "request": request,
        "departments": departments,
        "site_url": settings["site_url"],
        "book_url": "/فرم-نوبت-دهی",
    }


@app.get("/", response_class=HTMLResponse)
def home(request: Request, session: Session = Depends(get_session)):
    ctx = site_context(session, request)
    ctx["articles"] = session.exec(
        select(Article).where(Article.published == True).order_by(Article.published_at.desc()).limit(6)
    ).all()
    return templates.TemplateResponse("pages/home.html", ctx)


@app.get("/دپارتمان‌ها", response_class=HTMLResponse)
@app.get("/departments", response_class=HTMLResponse)
def departments_list(request: Request, session: Session = Depends(get_session)):
    ctx = site_context(session, request)
    return templates.TemplateResponse("pages/departments.html", ctx)


@app.get("/دپارتمان-{slug}", response_class=HTMLResponse)
@app.get("/departments/{slug}", response_class=HTMLResponse)
def department_detail(slug: str, request: Request, session: Session = Depends(get_session)):
    dept = session.exec(select(Department).where(Department.slug == slug, Department.published == True)).first()
    if not dept:
        raise HTTPException(status_code=404, detail="دپارتمان پیدا نشد")
    ctx = site_context(session, request)
    ctx.update({
        "department": dept,
        "consultants": session.exec(
            select(Consultant).where(
                Consultant.department_id == dept.id,
                Consultant.published == True,
            ).order_by(Consultant.sort_order)
        ).all(),
    })
    return templates.TemplateResponse("pages/department.html", ctx)


@app.get("/مشاوران", response_class=HTMLResponse)
@app.get("/consultants", response_class=HTMLResponse)
def consultants_list(request: Request, session: Session = Depends(get_session)):
    ctx = site_context(session, request)
    ctx["consultants"] = session.exec(
        select(Consultant).where(Consultant.published == True).order_by(Consultant.sort_order)
    ).all()
    return templates.TemplateResponse("pages/consultants.html", ctx)


@app.get("/مشاور/{slug}", response_class=HTMLResponse)
@app.get("/consultants/{slug}", response_class=HTMLResponse)
def consultant_detail(slug: str, request: Request, session: Session = Depends(get_session)):
    person = session.exec(select(Consultant).where(Consultant.slug == slug, Consultant.published == True)).first()
    if not person:
        raise HTTPException(status_code=404, detail="مشاور پیدا نشد")
    ctx = site_context(session, request)
    ctx["consultant"] = person
    return templates.TemplateResponse("pages/consultant_detail.html", ctx)


@app.get("/blog/{slug}", response_class=HTMLResponse)
def article_detail(slug: str, request: Request, session: Session = Depends(get_session)):
    article = session.exec(select(Article).where(Article.slug == slug, Article.published == True)).first()
    if not article:
        raise HTTPException(status_code=404, detail="مقاله پیدا نشد")
    ctx = site_context(session, request)
    ctx["article"] = article
    return templates.TemplateResponse("pages/article.html", ctx)


@app.get("/health")
def health():
    return {"ok": True}
