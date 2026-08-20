# کلینیک دلسا — سایت جدید (جایگزین WordPress)

[![CI](https://github.com/delsaclinic/delsa-clinic-homepage/actions/workflows/ci.yml/badge.svg)](https://github.com/delsaclinic/delsa-clinic-homepage/actions/workflows/ci.yml)
[![Pages](https://github.com/delsaclinic/delsa-clinic-homepage/actions/workflows/pages.yml/badge.svg)](https://github.com/delsaclinic/delsa-clinic-homepage/actions/workflows/pages.yml)

**پیش‌نمایش استاتیک:** https://delsaclinic.github.io/delsa-clinic-homepage/

## ساختار

| پوشه | توضیح |
|------|--------|
| `backend/` | FastAPI + SQLAdmin + صفحات دپارتمان/مشاوران |
| `index.html` | صفحه اصلی HTML (نسخه قبلی) |
| `header.php` / `footer.php` | هدر/فوتر وردپرس (legacy) |
| `wpcode-*.php` | اسنیپت‌های WPCode |
| `wordpress/` | محتوای WPBakery |
| `scripts/build_pages.py` | export استاتیک برای GitHub Pages |

## اجرای لوکال (بکند)

```bash
cd backend
python3 -m venv .venv && source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
python -m app.seed
uvicorn app.main:app --reload --port 8000
```

- سایت: http://localhost:8000
- ادمین: http://localhost:8000/admin (`admin` / `admin`)

## تست

```bash
cd backend && pip install pytest httpx && pytest tests/ -v
```

## GitHub Pages (استاتیک)

```bash
pip install -r backend/requirements.txt httpx
python scripts/build_pages.py
# خروجی در docs/
```

CI روی هر push به `main`:
1. **CI** — تست بکند
2. **Pages** — build + deploy به GitHub Pages

## صفحات

- `/` — خانه
- `/دپارتمان-مشاوره-شغلی` — مشاوره شغلی
- `/دپارتمان-کودک-و-نوجوان` — کودک و نوجوان
- `/مشاوران` — لیست مشاوران
- `/مشاور/سپیده-آزرم` — پروفایل مشاور

## Legacy WordPress

فایل‌های `wpcode-*`, `header.php`, `elementor-*` برای سایت فعلی `delsaclinic.com` نگه داشته شده‌اند تا مهاجرت کامل انجام شود.
