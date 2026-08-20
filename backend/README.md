# Backend — کلینیک دلسا

FastAPI + SQLModel + SQLAdmin

## راه‌اندازی سریع

```bash
cd backend
python3 -m venv .venv && source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
python -m app.seed
uvicorn app.main:app --reload --port 8000
```

- **سایت:** http://localhost:8000
- **ادمین:** http://localhost:8000/admin
- **آپلود:** http://localhost:8000/admin/media-upload
- **ورود پیش‌فرض:** `admin` / `admin`

## مدل‌های داده

| مدل | کاربرد |
|-----|--------|
| `Department` | دپارتمان‌ها |
| `Consultant` | مشاوران |
| `Article` | مقالات وبلاگ |
| `Page` | صفحات ثابت (درباره ما) |
| `SiteSettings` | تلفن، آدرس، لوگو، نقشه |
| `AppointmentRequest` | درخواست‌های فرم نوبت |

## آپلود تصویر

1. وارد `/admin` شو
2. برو به **آپلود تصویر**
3. فایل را آپلود کن
4. آدرس `/uploads/...` را در فیلد `image_url` دپارتمان/مشاور/مقاله paste کن

API: `POST /admin/api/upload` (با session ادمین)

## Seed

```bash
python -m app.seed
```

فقط اگر DB خالی باشد اجرا می‌شود. شامل ۶ دپارتمان، ۹ مشاور، صفحه درباره ما، یک مقاله نمونه.

## متغیرهای محیطی (`.env`)

```
DATABASE_URL=sqlite:///./delsa.db
SECRET_KEY=change-me
ADMIN_USERNAME=admin
ADMIN_PASSWORD=admin
SITE_URL=http://localhost:8000
```

## تست

```bash
DATABASE_URL=sqlite:///:memory: pytest tests/ -v
```
