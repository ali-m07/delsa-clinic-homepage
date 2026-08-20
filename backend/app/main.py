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
from app.models import AppointmentFormField, AppointmentRequest, Article, Consultant, Department, Page, SiteSettings
from app.uploads import ensure_upload_dir, save_upload

import json as _json
from datetime import datetime as _dt

BASE_DIR = Path(__file__).resolve().parent
UPLOAD_DIR = BASE_DIR.parent / "uploads"
templates = Jinja2Templates(directory=str(BASE_DIR / "templates"))
templates.env.filters["from_json"] = lambda s: _json.loads(s) if s else []
templates.env.globals["now"] = _dt.utcnow

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
        "settings": get_site_settings(session),
        "book_url": "/فرم-نوبت-دهی",
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
    ctx["consultants"] = session.exec(
        select(Consultant).where(Consultant.published == True).order_by(Consultant.sort_order)
    ).all()
    return templates.TemplateResponse("pages/about.html", ctx)


@app.get("/فرم-نوبت-دهی", response_class=HTMLResponse)
@app.get("/appointment", response_class=HTMLResponse)
def appointment_form(request: Request, session: Session = Depends(get_session)):
    ctx = site_context(session, request)
    ctx["success"] = request.query_params.get("success") == "1"
    ctx["dynamic_fields"] = session.exec(
        select(AppointmentFormField)
        .where(AppointmentFormField.is_active == True)
        .order_by(AppointmentFormField.sort_order)
    ).all()
    return templates.TemplateResponse("pages/appointment.html", ctx)


@app.post("/فرم-نوبت-دهی", response_class=HTMLResponse)
@app.post("/appointment", response_class=HTMLResponse)
async def appointment_submit(request: Request, session: Session = Depends(get_session)):
    form_data = await request.form()

    full_name = form_data.get("full_name", "").strip()
    phone = form_data.get("phone", "").strip()
    email = form_data.get("email", "").strip()
    department = form_data.get("department", "").strip()
    province = form_data.get("province", "").strip()
    city = form_data.get("city", "").strip()
    preferred_date = form_data.get("preferred_date", "").strip()
    session_type = form_data.get("session_type", "").strip()
    notes = form_data.get("notes", "").strip()

    if not full_name or not phone:
        dynamic_fields = session.exec(
            select(AppointmentFormField)
            .where(AppointmentFormField.is_active == True)
            .order_by(AppointmentFormField.sort_order)
        ).all()
        ctx = site_context(session, request)
        ctx.update({
            "error": "نام و شماره تماس الزامی است.",
            "form": dict(form_data),
            "dynamic_fields": dynamic_fields,
        })
        return templates.TemplateResponse("pages/appointment.html", ctx)

    # Collect dynamic field values into JSON
    import json as _json
    dynamic_fields = session.exec(
        select(AppointmentFormField).where(AppointmentFormField.is_active == True)
    ).all()
    extra = {f.label: form_data.get(f"field_{f.id}", "") for f in dynamic_fields}

    record = AppointmentRequest(
        full_name=full_name,
        phone=phone,
        email=email,
        department=department,
        province=province,
        city=city,
        preferred_date=preferred_date,
        session_type=session_type,
        notes=notes,
        extra_data=_json.dumps(extra, ensure_ascii=False) if extra else "",
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
