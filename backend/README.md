# کلینیک دلسا — Backend (جایگزین WordPress)

**Python + FastAPI + SQLModel + SQLAdmin**

- سایت: `http://localhost:8000`
- پنل مدیریت (CMS): `http://localhost:8000/admin`
- پیش‌فرض ورود: `admin` / `admin` (در `.env` عوض کن)

## چرا این استک؟

| گزینه | وزن | CMS آماده |
|--------|-----|-----------|
| **FastAPI + SQLAdmin** ✅ | سبک | پنل ادمین خودکار |
| Wagtail (Django) | سنگین‌تر | عالی ولی overkill |
| PocketBase (Go) | خیلی سبک | ادمین دارد، فرانت جدا |

## راه‌اندازی

```bash
cd backend
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
python -m app.seed
uvicorn app.main:app --reload --port 8000
```

## مسیرها

| URL | توضیح |
|-----|--------|
| `/` | صفحه اصلی |
| `/دپارتمان-مشاوره-شغلی` | صفحه دپارتمان |
| `/مشاوران` | لیست مشاوران |
| `/مشاور/سپیده-آزرم` | پروفایل مشاور |
| `/admin` | ویرایش محتوا |

## مدل داده

- **Department** — دپارتمان (عنوان، اسلاگ، intro، body HTML، تصویر، SEO)
- **Consultant** — مشاور (نام، نقش، بیو، تصویر، ارتباط با دپارتمان‌ها)
- **Article** — مقاله

همه‌چیز از پنل `/admin` قابل ویرایش است — بدون WPBakery، بدون Elementor.

## مهاجرت از WordPress

1. محتوای دپارتمان‌ها را در ادمین paste کن
2. مشاوران را بساز و به دپارتمان لینک کن
3. روی سرور nginx: 301 از URLهای قدیمی WP
4. فرم نوبت / بلاگ را در فاز بعد وصل کن

## Production

```bash
# PostgreSQL
DATABASE_URL=postgresql://user:pass@localhost/delsa
SECRET_KEY=...
ADMIN_PASSWORD=...

uvicorn app.main:app --host 0.0.0.0 --port 8000
```

## فاز بعدی (اختیاری)

- [ ] آپلود تصویر (S3 یا local)
- [ ] import از WordPress REST API
- [ ] صفحه اصلی کامل از `index.html`
- [ ] فرم نوبت‌دهی
- [ ] Docker Compose
