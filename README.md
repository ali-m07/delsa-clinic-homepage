# کلینیک دلسا — سایت و CMS

[![CI](https://github.com/ali-m07/delsa-clinic-homepage/actions/workflows/ci.yml/badge.svg)](https://github.com/ali-m07/delsa-clinic-homepage/actions/workflows/ci.yml)
[![Pages](https://github.com/ali-m07/delsa-clinic-homepage/actions/workflows/pages.yml/badge.svg)](https://github.com/ali-m07/delsa-clinic-homepage/actions/workflows/pages.yml)

**پیش‌نمایش استاتیک:** https://ali-m07.github.io/delsa-clinic-homepage/

**ریپو:** https://github.com/ali-m07/delsa-clinic-homepage

---

## چی ساخته شده؟

| بخش | توضیح |
|-----|--------|
| **سایت** | صفحه اصلی، دپارتمان‌ها، مشاوران، وبلاگ، درباره ما، فرم نوبت |
| **CMS / ادمین** | `/admin` — اضافه/حذف/ویرایش دپارتمان، مشاور، مقاله، صفحات |
| **آپلود تصویر** | `/admin/media-upload` — آپلود و کپی آدرس در فیلد تصویر |
| **درخواست نوبت** | فرم `/فرم-نوبت-دهی` → ذخیره در DB → مشاهده در پنل ادمین |
| **GitHub Pages** | export استاتیک در `docs/` با هر push |
| **CI** | pytest روی هر push/PR |

---

## اجرای لوکال

```bash
cd backend
python3 -m venv .venv && source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
python -m app.seed
uvicorn app.main:app --reload --port 8000
```

| آدرس | کاربرد |
|------|--------|
| http://localhost:8000 | سایت |
| http://localhost:8000/admin | پنل مدیریت (`admin` / `admin`) |
| http://localhost:8000/admin/media-upload | آپلود تصویر |
| http://localhost:8000/فرم-نوبت-دهی | فرم نوبت |

---

## پنل ادمین — چه کارهایی می‌شود؟

- **دپارتمان‌ها** — عنوان، متن HTML، تصویر، SEO، ترتیب نمایش
- **مشاوران** — نام، نقش، بیو، تصویر، دپارتمان
- **مقالات** — وبلاگ
- **صفحات** — مثلاً «درباره ما»
- **درخواست‌های نوبت** — لیست فرم‌های ارسالی + تغییر وضعیت
- **تنظیمات سایت** — تلفن، آدرس، واتساپ، نقشه، هزینه رزرو
- **آپلود تصویر** — فایل → آدرس `/uploads/...` برای paste در فیلد تصویر

---

## صفحات سایت

| URL | صفحه |
|-----|------|
| `/` | خانه (hero، خدمات، FAQ، ...) |
| `/فرم-نوبت-دهی` | فرم رزرو نوبت |
| `/درباره-ما` | درباره کلینیک |
| `/blog` | لیست مقالات |
| `/مشاوران` | لیست مشاوران |
| `/دپارتمان-{slug}` | صفحه دپارتمان |
| `/مشاور/{slug}` | پروفایل مشاور |

---

## تست

```bash
cd backend
DATABASE_URL=sqlite:///:memory: pytest tests/ -v
```

## GitHub Pages (build دستی)

```bash
pip install -r backend/requirements.txt httpx
python scripts/build_pages.py
# خروجی در docs/
```

> **توجه:** روی GitHub Pages فقط HTML استاتیک است. فرم نوبت و پنل ادمین روی سرور FastAPI کار می‌کنند.

---

## ساختار پوشه‌ها

```
backend/           FastAPI + SQLAdmin + templates
  app/
  uploads/         تصاویر آپلودشده
docs/              خروجی GitHub Pages (auto-generated)
scripts/           build_pages.py
index.html         نسخه HTML مستقل (legacy)
wpcode-*.php       اسنیپت‌های WordPress (legacy)
```

---

## Legacy WordPress

فایل‌های `wpcode-*`, `header.php`, `elementor-*` برای سایت فعلی `delsaclinic.com` نگه داشته شده‌اند. محتوای seed از سایت live گرفته شده؛ برای جزئیات بیشتر از پنل ادمین ویرایش کنید.

---

## Production

```bash
DATABASE_URL=postgresql://user:pass@localhost/delsa
SECRET_KEY=...
ADMIN_PASSWORD=...
SITE_URL=https://delsaclinic.com

uvicorn app.main:app --host 0.0.0.0 --port 8000
```

پشت nginx/caddy با SSL. برای PostgreSQL در production توصیه می‌شود.
