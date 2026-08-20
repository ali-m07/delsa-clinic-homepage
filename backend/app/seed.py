"""Seed initial content. Run: python -m app.seed"""

from sqlmodel import Session, select

from app.database import engine, init_db
from app.models import Consultant, Department

CAREER_BODY = """
<h2>مشاوره شغلی چیست؟</h2>
<p><strong>مشاوره شغلی</strong> (راهنمایی شغلی) فرآیندی هدفمند برای کمک به افراد در شناخت توانمندی‌ها، علایق و فرصت‌های شغلی است.</p>

<h2>چه زمانی به مشاوره شغلی نیاز داریم؟</h2>
<ul>
<li><strong>انتخاب رشته و شغل اولیه</strong></li>
<li><strong>تغییر مسیر شغلی</strong></li>
<li><strong>مدیریت استرس شغلی</strong></li>
<li><strong>توسعه مهارت‌های حرفه‌ای</strong></li>
</ul>

<h2>خدمات دپارتمان مشاوره شغلی کلینیک دلسا</h2>
<ul>
<li>ارزیابی علایق، مهارت‌ها و ویژگی‌های شخصیتی</li>
<li>راهنمایی در انتخاب رشته تحصیلی و شغل مناسب</li>
<li>مشاوره برای تغییر شغل و طراحی مسیر حرفه‌ای جدید</li>
<li>کمک به مدیریت استرس و تصمیم‌گیری در شرایط اقتصادی دشوار</li>
<li>برنامه‌ریزی برای رشد شغلی و تقویت مهارت‌های نرم</li>
</ul>

<blockquote><p><strong>«شغل مناسب، جایی است که توانایی شما با نیاز جامعه و علاقه درونی‌تان هم‌راستا باشد.»</strong></p></blockquote>
"""

DEPARTMENTS = [
    {
        "slug": "روانپزشکی",
        "title": "دپارتمان روان‌پزشکی",
        "nav_label": "روان‌پزشکی",
        "sort_order": 1,
        "intro": "خدمات تخصصی روان‌پزشکی کلینیک دلسا.",
        "image_url": "https://delsaclinic.com/wp-content/uploads/2017/03/gallery-7-1-370x385.jpg",
    },
    {
        "slug": "روان-درمانی",
        "title": "دپارتمان روان‌درمانی",
        "nav_label": "روان‌درمانی",
        "sort_order": 2,
        "intro": "خدمات روان‌درمانی فردی و گروهی.",
        "image_url": "https://delsaclinic.com/wp-content/uploads/2017/03/gallery-7-1-370x385.jpg",
    },
    {
        "slug": "زوج-و-خانواده",
        "title": "دپارتمان زوج و خانواده",
        "nav_label": "زوج و خانواده",
        "sort_order": 3,
        "intro": "مشاوره زوجین و خانواده.",
        "image_url": "https://delsaclinic.com/wp-content/uploads/2017/03/gallery-7-1-370x385.jpg",
    },
    {
        "slug": "کودک-و-نوجوان",
        "title": "دپارتمان کودک و نوجوان",
        "nav_label": "کودک و نوجوان",
        "sort_order": 4,
        "intro": "مشاوره تخصصی کودک و نوجوان.",
        "image_url": "https://delsaclinic.com/wp-content/uploads/2017/03/gallery-7-1-370x385.jpg",
    },
    {
        "slug": "ترک-اعتیاد",
        "title": "دپارتمان ترک اعتیاد",
        "nav_label": "ترک اعتیاد",
        "sort_order": 5,
        "intro": "درمان و حمایت ترک اعتیاد.",
        "image_url": "https://delsaclinic.com/wp-content/uploads/2017/03/gallery-7-1-370x385.jpg",
    },
    {
        "slug": "مشاوره-شغلی",
        "title": "دپارتمان مشاوره شغلی",
        "nav_label": "مشاوره شغلی",
        "sort_order": 6,
        "intro": (
            "دپارتمان مشاوره شغلی کلینیک دلسا، خدمات تخصصی راهنمایی شغلی را برای افرادی ارائه می‌دهد "
            "که در انتخاب، تغییر یا ادامه مسیر حرفه‌ای خود به همراهی حرفه‌ای نیاز دارند."
        ),
        "body_html": CAREER_BODY,
        "image_url": "https://delsaclinic.com/wp-content/uploads/2017/03/gallery-7-1-370x385.jpg",
        "meta_title": "مشاوره شغلی | کلینیک دلسا",
        "meta_description": "مشاوره شغلی در کلینیک دلسا: انتخاب شغل، تغییر مسیر حرفه‌ای و مدیریت استرس کاری.",
    },
]

CONSULTANTS = [
    {
        "slug": "سپیده-آزرم",
        "name": "سپیده آزرم",
        "role": "مشاور شغلی",
        "image_url": "https://delsaclinic.com/wp-content/uploads/2022/10/maryam-salehi-240x274.jpg",
        "department_slug": "مشاوره-شغلی",
    },
    {
        "slug": "میکایا-مهروز",
        "name": "میکایا مهروز",
        "role": "مشاور شغلی",
        "image_url": "https://delsaclinic.com/wp-content/uploads/2022/11/hasan-akbarzadeh-240x274.jpg",
        "department_slug": "مشاوره-شغلی",
    },
]


def seed() -> None:
    init_db()
    with Session(engine) as session:
        if session.exec(select(Department)).first():
            return

        dept_map = {}
        for item in DEPARTMENTS:
            data = dict(item)
            body_html = data.pop("body_html", None)
            dept = Department(**data)
            dept.body_html = body_html or f"<p>{dept.intro}</p>"
            session.add(dept)
            session.flush()
            dept_map[dept.slug] = dept

        for raw in CONSULTANTS:
            item = dict(raw)
            dept_slug = item.pop("department_slug")
            person = Consultant(**item, department_id=dept_map[dept_slug].id)
            session.add(person)

        session.commit()
        print("Seeded departments and consultants.")


if __name__ == "__main__":
    seed()
