"""Seed initial content. Run: python -m app.seed"""

from datetime import datetime

from sqlmodel import Session, select

from app.database import engine, init_db
from app.models import Article, Consultant, Department, Page, SiteSettings

IMG = "https://delsaclinic.com/wp-content/uploads/2017/03/gallery-7-1-370x385.jpg"
CONSULTANT_IMG = "https://delsaclinic.com/wp-content/uploads/2022/10/maryam-salehi-240x274.jpg"

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
</ul>
<blockquote><p><strong>«شغل مناسب، جایی است که توانایی شما با نیاز جامعه و علاقه درونی‌تان هم‌راستا باشد.»</strong></p></blockquote>
"""

DEPARTMENTS = [
    {
        "slug": "روان-درمانی",
        "title": "دپارتمان روان‌درمانی",
        "nav_label": "روان‌درمانی",
        "sort_order": 1,
        "intro": "اگر احساساتتان سنگین شده، با رویکرد علمی کنارتان هستیم.",
        "body_html": """
<h2>روان‌درمانی در کلینیک دلسا</h2>
<p>روان‌درمانی فرآیندی ساختارمند برای شناخت و تغییر الگوهای فکری، احساسی و رفتاری است که کیفیت زندگی را تحت تأثیر قرار می‌دهند.</p>
<h2>چه زمانی مراجعه کنیم؟</h2>
<ul>
<li>افسردگی، اضطراب یا حملات پانیک</li>
<li>استرس مزمن و فرسودگی</li>
<li>مشکلات خواب، تمرکز یا انرژی</li>
<li>بازسازی پس از بحران یا ضربه</li>
</ul>
<p>تیم روان‌درمانی دلسا با رویکردهای شناختی-رفتاری، طرحواره‌درمانی و درمان مبتنی بر ذهن‌آگاهی همراه شماست.</p>
""",
        "image_url": IMG,
    },
    {
        "slug": "روانپزشکی",
        "title": "دپارتمان روان‌پزشکی",
        "nav_label": "روان‌پزشکی",
        "sort_order": 5,
        "intro": "ارزیابی و درمان تخصصی، با آرامش و دقت.",
        "body_html": """
<h2>خدمات روان‌پزشکی</h2>
<p>دپارتمان روان‌پزشکی کلینیک دلسا ارزیابی، تشخیص و درمان اختلالات روانی را با رویکردی علمی و انسانی ارائه می‌دهد.</p>
<ul>
<li>ویزیت و معاینه تخصصی</li>
<li>تجویز دارو در صورت نیاز و پیگیری درمان</li>
<li>همکاری نزدیک با تیم روان‌درمانی</li>
</ul>
""",
        "image_url": IMG,
    },
    {
        "slug": "زوج-و-خانواده",
        "title": "دپارتمان زوج و خانواده",
        "nav_label": "زوج و خانواده",
        "sort_order": 3,
        "intro": "برای بهبود رابطه و گفت‌وگوی سالم‌تر، همراهتان هستیم.",
        "body_html": """
<h2>مشاوره زوج و خانواده</h2>
<p>روابط سالم نیازمند گفت‌وگو، شنیده شدن و یادگیری مهارت‌های جدید است. در این دپارتمان، زوجین و خانواده‌ها برای بهبود ارتباط و حل تعارض همراهی می‌شوند.</p>
<ul>
<li>مشاوره زوجین</li>
<li>مشاوره خانواده</li>
<li>مدیریت تعارض و بحران‌های رابطه</li>
</ul>
""",
        "image_url": IMG,
    },
    {
        "slug": "کودک-و-نوجوان",
        "title": "دپارتمان کودک و نوجوان",
        "nav_label": "کودک و نوجوان",
        "sort_order": 4,
        "intro": "در مسیر رشد سالم فرزندتان، پشتیبان شما.",
        "body_html": """
<h2>مشاوره کودک و نوجوان</h2>
<p>کودکان و نوجوانان با چالش‌های منحصربه‌فردی در مسیر رشد روبه‌رو هستند. تیم تخصصی دلسا با رویکردهای متناسب با سن، به والدین و فرزندان کمک می‌کند.</p>
<ul>
<li>مشکلات رفتاری و هیجانی</li>
<li>اضطراب، افسردگی و استرس تحصیلی</li>
<li>مشاوره والدین</li>
</ul>
""",
        "image_url": IMG,
    },
    {
        "slug": "ترک-اعتیاد",
        "title": "دپارتمان ترک اعتیاد",
        "nav_label": "ترک اعتیاد",
        "sort_order": 6,
        "intro": "درمان و حمایت همراه با احترام و محرمانگی.",
        "body_html": """
<h2>ترک اعتیاد</h2>
<p>ترک اعتیاد مسیر دشواری است که با حمایت تخصصی قابل عبور است. تیم دلسا در کنار فرد و خانواده، برنامه درمانی شخصی‌سازی‌شده ارائه می‌دهد.</p>
<ul>
<li>ارزیابی و برنامه‌ریزی درمان</li>
<li>مشاوره فردی و خانوادگی</li>
<li>پیگیری و پیشگیری از عود</li>
</ul>
""",
        "image_url": IMG,
    },
    {
        "slug": "مشاوره-شغلی",
        "title": "دپارتمان مشاوره شغلی",
        "nav_label": "مشاوره شغلی",
        "sort_order": 7,
        "intro": (
            "دپارتمان مشاوره شغلی کلینیک دلسا، خدمات تخصصی راهنمایی شغلی را برای افرادی ارائه می‌دهد "
            "که در انتخاب، تغییر یا ادامه مسیر حرفه‌ای خود به همراهی حرفه‌ای نیاز دارند."
        ),
        "body_html": CAREER_BODY,
        "image_url": IMG,
        "meta_title": "مشاوره شغلی | کلینیک دلسا",
        "meta_description": "مشاوره شغلی در کلینیک دلسا: انتخاب شغل، تغییر مسیر حرفه‌ای و مدیریت استرس کاری.",
    },
]

CONSULTANTS = [
    {"slug": "الهام-مصباحی", "name": "الهام مصباحی", "role": "روان‌شناس", "department_slug": "روان-درمانی", "sort_order": 1,
     "image_url": "https://delsaclinic.com/wp-content/uploads/2022/10/elham-mosbahi-240x274.jpg"},
    {"slug": "مریم-صالحی", "name": "مریم صالحی", "role": "روان‌شناس", "department_slug": "کودک-و-نوجوان", "sort_order": 2,
     "image_url": CONSULTANT_IMG},
    {"slug": "حسن-اکبرزاده", "name": "حسن اکبرزاده", "role": "روان‌شناس", "department_slug": "روان-درمانی", "sort_order": 3,
     "image_url": "https://delsaclinic.com/wp-content/uploads/2022/11/hasan-akbarzadeh-240x274.jpg"},
    {"slug": "دکتر-رباب-حامدی", "name": "دکتر رباب حامدی", "role": "روان‌پزشک", "department_slug": "روانپزشکی", "sort_order": 4,
     "image_url": "https://delsaclinic.com/wp-content/uploads/2022/11/rabab-hamedi-240x274.jpg"},
    {"slug": "دکتر-نسرین-مصباح", "name": "دکتر نسرین مصباح", "role": "روان‌پزشک", "department_slug": "زوج-و-خانواده", "sort_order": 5,
     "image_url": "https://delsaclinic.com/wp-content/uploads/2022/11/nasrin-mosbah-240x274.jpg"},
    {"slug": "دکتر-نسرین-دانایی", "name": "دکتر نسرین دانایی", "role": "روان‌پزشک", "department_slug": "روانپزشکی", "sort_order": 6},
    {"slug": "فاطمه-حسین-پور", "name": "فاطمه حسین‌پور", "role": "روان‌شناس", "department_slug": "کودک-و-نوجوان", "sort_order": 7},
    {"slug": "سپیده-آزرم", "name": "سپیده آزرم", "role": "مشاور شغلی", "department_slug": "مشاوره-شغلی", "sort_order": 8},
    {"slug": "میکایا-مهروز", "name": "میکایا مهروز", "role": "مشاور شغلی", "department_slug": "مشاوره-شغلی", "sort_order": 9},
]

ABOUT_BODY = """
<h2>درباره کلینیک دلسا</h2>
<p>کلینیک دلسا گروه تخصصی مشاوره و خدمات روان‌شناختی در تهران است که بیش از ۱۵ سال در مسیر سلامت روان همراه مراجعان بوده است.</p>
<h2>ماموریت ما</h2>
<p>ارائه خدمات تخصصی روان‌شناسی و روان‌پزشکی با رویکردی علمی، انسانی و محرمانه — در فضایی آرام و قابل اعتماد.</p>
<h2>خدمات ما</h2>
<ul>
<li>روان‌درمانی و مشاوره فردی</li>
<li>مشاوره زوج و خانواده</li>
<li>مشاوره کودک و نوجوان</li>
<li>روان‌پزشکی</li>
<li>مشاوره شغلی</li>
</ul>
<p>برای رزرو نوبت می‌توانید فرم آنلاین را تکمیل کنید یا با شماره‌های تماس کلینیک تماس بگیرید.</p>
"""


def seed() -> None:
    init_db()
    with Session(engine) as session:
        if session.exec(select(Department)).first():
            return

        if not session.get(SiteSettings, 1):
            session.add(SiteSettings())

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
            image_url = item.pop("image_url", CONSULTANT_IMG)
            person = Consultant(**item, image_url=image_url, department_id=dept_map[dept_slug].id)
            session.add(person)

        session.add(Page(
            slug="درباره-ما",
            title="درباره ما",
            body_html=ABOUT_BODY,
            meta_title="درباره ما | کلینیک دلسا",
            meta_description="آشنایی با کلینیک دلسا — گروه تخصصی مشاوره و خدمات روان‌شناختی در تهران.",
        ))

        session.add(Article(
            slug="سلامت-روان-در-کار",
            title="سلامت روان در محیط کار",
            excerpt="چطور استرس شغلی را مدیریت کنیم و تعادل بین کار و زندگی برقرار کنیم.",
            body_html="<p>استرس شغلی یکی از چالش‌های رایج امروز است. شناخت علائم، مرزگذاری سالم و درخواست کمک حرفه‌ای از راهکارهای مؤثر هستند.</p>",
            published_at=datetime(2024, 6, 1),
        ))

        session.commit()
        print("Seeded site content.")
