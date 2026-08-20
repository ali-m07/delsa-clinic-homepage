from sqladmin import Admin, BaseView, ModelView, expose
from sqladmin.authentication import AuthenticationBackend
from starlette.requests import Request
from starlette.responses import HTMLResponse

from app.config import get_settings
from app.models import AppointmentRequest, Article, Consultant, Department, Page, SiteSettings
from app.uploads import save_upload


class AdminAuth(AuthenticationBackend):
    async def login(self, request: Request) -> bool:
        form = await request.form()
        settings = get_settings()
        if (
            form.get("username") == settings["admin_username"]
            and form.get("password") == settings["admin_password"]
        ):
            request.session.update({"authenticated": True})
            return True
        return False

    async def logout(self, request: Request) -> bool:
        request.session.clear()
        return True

    async def authenticate(self, request: Request) -> bool:
        return bool(request.session.get("authenticated"))


class DepartmentAdmin(ModelView, model=Department):
    name = "دپارتمان"
    name_plural = "دپارتمان‌ها"
    column_list = [Department.id, Department.title, Department.slug, Department.published, Department.sort_order]
    column_searchable_list = [Department.title, Department.slug]
    column_sortable_list = [Department.sort_order, Department.title]
    form_columns = [
        Department.title,
        Department.nav_label,
        Department.slug,
        Department.intro,
        Department.body_html,
        Department.image_url,
        Department.sort_order,
        Department.published,
        Department.meta_title,
        Department.meta_description,
    ]
    form_widget_args = {
        "body_html": {"rows": 12, "style": "font-family: monospace; direction: rtl;"},
        "intro": {"rows": 3},
        "image_url": {"placeholder": "/uploads/... یا آدرس کامل"},
    }


class ConsultantAdmin(ModelView, model=Consultant):
    name = "مشاور"
    name_plural = "مشاوران"
    column_list = [Consultant.id, Consultant.name, Consultant.slug, Consultant.published, Consultant.sort_order]
    column_searchable_list = [Consultant.name, Consultant.slug]
    form_columns = [
        Consultant.name,
        Consultant.slug,
        Consultant.role,
        Consultant.bio_html,
        Consultant.image_url,
        Consultant.sort_order,
        Consultant.published,
        Consultant.department_id,
    ]
    form_widget_args = {
        "bio_html": {"rows": 10, "style": "font-family: monospace; direction: rtl;"},
        "image_url": {"placeholder": "/uploads/... یا آدرس کامل"},
    }


class ArticleAdmin(ModelView, model=Article):
    name = "مقاله"
    name_plural = "مقالات"
    column_list = [Article.id, Article.title, Article.slug, Article.published, Article.published_at]
    column_searchable_list = [Article.title, Article.slug]
    form_columns = [
        Article.title,
        Article.slug,
        Article.excerpt,
        Article.body_html,
        Article.image_url,
        Article.published,
        Article.published_at,
    ]
    form_widget_args = {
        "body_html": {"rows": 12, "style": "font-family: monospace; direction: rtl;"},
        "image_url": {"placeholder": "/uploads/... یا آدرس کامل"},
    }


class PageAdmin(ModelView, model=Page):
    name = "صفحه"
    name_plural = "صفحات"
    column_list = [Page.id, Page.title, Page.slug, Page.published]
    column_searchable_list = [Page.title, Page.slug]
    form_columns = [
        Page.title,
        Page.slug,
        Page.body_html,
        Page.published,
        Page.meta_title,
        Page.meta_description,
    ]
    form_widget_args = {
        "body_html": {"rows": 14, "style": "font-family: monospace; direction: rtl;"},
    }


class SiteSettingsAdmin(ModelView, model=SiteSettings):
    name = "تنظیمات سایت"
    name_plural = "تنظیمات سایت"
    can_create = False
    can_delete = False
    column_list = [SiteSettings.id, SiteSettings.phone_mobile, SiteSettings.email]
    form_columns = [
        SiteSettings.phone_mobile,
        SiteSettings.phone_landline,
        SiteSettings.phone_landline2,
        SiteSettings.email,
        SiteSettings.whatsapp,
        SiteSettings.address,
        SiteSettings.map_embed_url,
        SiteSettings.booking_fee_note,
        SiteSettings.hero_image_url,
        SiteSettings.logo_url,
    ]
    form_widget_args = {
        "address": {"rows": 3},
        "map_embed_url": {"rows": 2},
    }


class AppointmentAdmin(ModelView, model=AppointmentRequest):
    name = "درخواست نوبت"
    name_plural = "درخواست‌های نوبت"
    can_create = False
    column_list = [
        AppointmentRequest.id,
        AppointmentRequest.full_name,
        AppointmentRequest.phone,
        AppointmentRequest.department,
        AppointmentRequest.status,
        AppointmentRequest.created_at,
    ]
    column_sortable_list = [AppointmentRequest.created_at, AppointmentRequest.status]
    column_default_sort = [(AppointmentRequest.created_at, True)]
    column_searchable_list = [AppointmentRequest.full_name, AppointmentRequest.phone, AppointmentRequest.email]
    form_columns = [
        AppointmentRequest.full_name,
        AppointmentRequest.phone,
        AppointmentRequest.email,
        AppointmentRequest.department,
        AppointmentRequest.province,
        AppointmentRequest.city,
        AppointmentRequest.preferred_date,
        AppointmentRequest.session_type,
        AppointmentRequest.notes,
        AppointmentRequest.status,
        AppointmentRequest.created_at,
    ]
    form_widget_args = {
        "notes": {"rows": 4},
        "created_at": {"readonly": True},
    }


class MediaUploadView(BaseView):
    name = "آپلود تصویر"
    icon = "fa-solid fa-cloud-arrow-up"

    @expose("/media-upload", methods=["GET", "POST"], identity="media-upload")
    async def media_upload(self, request: Request):
        message = ""
        uploaded_url = ""
        if request.method == "POST":
            form = await request.form()
            file = form.get("file")
            if file and hasattr(file, "filename") and file.filename:
                try:
                    uploaded_url = await save_upload(file)
                    message = "آپلود موفق — آدرس را در فیلد تصویر کپی کنید."
                except Exception as exc:
                    message = str(getattr(exc, "detail", exc))
            else:
                message = "لطفاً یک فایل تصویر انتخاب کنید."

        html = f"""
        <!DOCTYPE html>
        <html lang="fa" dir="rtl">
        <head>
          <meta charset="utf-8">
          <title>آپلود تصویر | پنل دلسا</title>
          <style>
            body {{ font-family: Tahoma, sans-serif; max-width: 560px; margin: 2rem auto; padding: 0 1rem; }}
            .card {{ border: 1px solid #ddd; border-radius: 12px; padding: 1.25rem; }}
            input[type=file] {{ margin: 1rem 0; }}
            button {{ background: #4CC9C0; border: 0; padding: .6rem 1.2rem; border-radius: 8px; cursor: pointer; }}
            .ok {{ color: #0a7; }}
            .err {{ color: #c00; }}
            code {{ background: #f4f4f4; padding: .35rem .5rem; border-radius: 6px; display: block; margin-top: .5rem; word-break: break-all; }}
            a {{ color: #1B4283; }}
          </style>
        </head>
        <body>
          <p><a href="/admin">← بازگشت به پنل</a></p>
          <div class="card">
            <h2>آپلود تصویر</h2>
            <p>فرمت‌های مجاز: JPG, PNG, WebP, GIF — حداکثر ۵ مگابایت</p>
            <form method="post" enctype="multipart/form-data">
              <input type="file" name="file" accept="image/*" required>
              <br>
              <button type="submit">آپلود</button>
            </form>
            {"<p class='ok'>" + message + "</p>" if message and uploaded_url else ""}
            {"<p class='err'>" + message + "</p>" if message and not uploaded_url else ""}
            {"<p>آدرس تصویر:</p><code>" + uploaded_url + "</code>" if uploaded_url else ""}
          </div>
        </body>
        </html>
        """
        return HTMLResponse(html)


def setup_admin(app, engine):
    settings = get_settings()
    authentication_backend = AdminAuth(secret_key=settings["secret_key"])
    admin = Admin(
        app,
        engine,
        authentication_backend=authentication_backend,
        title="پنل کلینیک دلسا",
        base_url="/admin",
    )
    admin.add_view(DepartmentAdmin)
    admin.add_view(ConsultantAdmin)
    admin.add_view(ArticleAdmin)
    admin.add_view(PageAdmin)
    admin.add_view(AppointmentAdmin)
    admin.add_view(SiteSettingsAdmin)
    admin.add_view(MediaUploadView)
    return admin
