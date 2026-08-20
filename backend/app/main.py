from pathlib import Path

from fastapi import Depends, FastAPI, Form, HTTPException, Request, UploadFile
from fastapi.responses import HTMLResponse, JSONResponse, RedirectResponse
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from sqlmodel import Session, select
from starlette.middleware.sessions import SessionMiddleware

from app.admin_views import setup_admin
from app.config import get_settings
from app.database import engine, get_session, init_db
from app.models import AppointmentRequest, Article, Consultant, Department, Page, SiteSettings
from app.uploads import ensure_upload_dir, save_upload

BASE_DIR = Path(__file__).resolve().parent
UPLOAD_DIR = BASE_DIR.parent / "uploads"
templates = Jinja2Templates(directory=str(BASE_DIR / "templates"))

app = FastAPI(title="Delsa Clinic", docs_url="/api/docs", redoc_url=None)
settings = get_settings()
app.add_middleware(SessionMiddleware, secret_key=settings["secret_key"])
app.mount("/static", StaticFiles(directory=str(BASE_DIR / "static")), name="static")
ensure_upload_dir()
app.mount("/uploads", StaticFiles(directory=str(UPLOAD_DIR)), name="uploads")
setup_admin(app, engine)


@app.on_event("startup")
def on_startup():
    init_db()


def get_site_settings(session: Session) -> SiteSettings:
    row = session.get(SiteSettings, 1)
    if not row:
        row = SiteSettings()
        session.add(row)
        session.commit()
        session.refresh(row)
    return row


def site_context(session: Session, request: Request) -> dict:
    departments = session.exec(
        select(Department).where(Department.published == True).order_by(Department.sort_order)
    ).all()
    return {
        "request": request,
        "departments": departments,
        "site_url": settings["site_url"],
        "book_url": "/فرم-نوبت-دهی",
        "settings": get_site_settings(session),
    }


@app.get("/", response_class=HTMLResponse)
def home(request: Request, session: Session = Depends(get_session)):
    ctx = site_context(session, request)
    ctx["articles"] = session.exec(
        select(Article).where(Article.published == True).order_by(Article.published_at.desc()).limit(3)
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
    if person.department_id:
        ctx["department"] = session.get(Department, person.department_id)
    return templates.TemplateResponse("pages/consultant_detail.html", ctx)


@app.get("/blog", response_class=HTMLResponse)
def blog_list(request: Request, session: Session = Depends(get_session)):
    ctx = site_context(session, request)
    ctx["articles"] = session.exec(
        select(Article).where(Article.published == True).order_by(Article.published_at.desc())
    ).all()
    return templates.TemplateResponse("pages/blog.html", ctx)


@app.get("/blog/{slug}", response_class=HTMLResponse)
def article_detail(slug: str, request: Request, session: Session = Depends(get_session)):
    article = session.exec(select(Article).where(Article.slug == slug, Article.published == True)).first()
    if not article:
        raise HTTPException(status_code=404, detail="مقاله پیدا نشد")
    ctx = site_context(session, request)
    ctx["article"] = article
    return templates.TemplateResponse("pages/article.html", ctx)


@app.get("/درباره-ما", response_class=HTMLResponse)
@app.get("/about", response_class=HTMLResponse)
def about_page(request: Request, session: Session = Depends(get_session)):
    page = session.exec(select(Page).where(Page.slug == "درباره-ما", Page.published == True)).first()
    if not page:
        raise HTTPException(status_code=404, detail="صفحه پیدا نشد")
    ctx = site_context(session, request)
    ctx["page"] = page
    return templates.TemplateResponse("pages/about.html", ctx)


@app.get("/فرم-نوبت-دهی", response_class=HTMLResponse)
@app.get("/appointment", response_class=HTMLResponse)
def appointment_form(request: Request, session: Session = Depends(get_session)):
    ctx = site_context(session, request)
    ctx["success"] = request.query_params.get("success") == "1"
    return templates.TemplateResponse("pages/appointment.html", ctx)


@app.post("/فرم-نوبت-دهی", response_class=HTMLResponse)
@app.post("/appointment", response_class=HTMLResponse)
def appointment_submit(
    request: Request,
    session: Session = Depends(get_session),
    full_name: str = Form(...),
    phone: str = Form(...),
    email: str = Form(""),
    department: str = Form(""),
    province: str = Form(""),
    city: str = Form(""),
    preferred_date: str = Form(""),
    session_type: str = Form(""),
    notes: str = Form(""),
):
    if not full_name.strip() or not phone.strip():
        ctx = site_context(session, request)
        ctx["error"] = "نام و شماره تماس الزامی است."
        ctx["form"] = {
            "full_name": full_name,
            "phone": phone,
            "email": email,
            "department": department,
            "province": province,
            "city": city,
            "preferred_date": preferred_date,
            "session_type": session_type,
            "notes": notes,
        }
        return templates.TemplateResponse("pages/appointment.html", ctx)

    record = AppointmentRequest(
        full_name=full_name.strip(),
        phone=phone.strip(),
        email=email.strip(),
        department=department.strip(),
        province=province.strip(),
        city=city.strip(),
        preferred_date=preferred_date.strip(),
        session_type=session_type.strip(),
        notes=notes.strip(),
    )
    session.add(record)
    session.commit()
    return RedirectResponse(url="/فرم-نوبت-دهی?success=1", status_code=303)


@app.post("/admin/api/upload")
async def admin_api_upload(request: Request, file: UploadFile):
    if not request.session.get("authenticated"):
        raise HTTPException(status_code=401, detail="ورود به پنل لازم است")
    url = await save_upload(file)
    return JSONResponse({"url": url})


@app.get("/health")
def health():
    return {"ok": True}
