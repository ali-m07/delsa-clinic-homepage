from sqladmin import Admin, BaseView, ModelView, expose
from sqladmin.authentication import AuthenticationBackend
from starlette.requests import Request
from starlette.responses import HTMLResponse
from wtforms import TextAreaField
from wtforms.widgets import TextArea

from app.config import get_settings
from app.models import (
    AppointmentFormField,
    AppointmentRequest,
    Article,
    Consultant,
    Department,
    Page,
    SiteSettings,
)
from app.uploads import save_upload

# ---------------------------------------------------------------------------
# TinyMCE injection script (self-hosted CDN, free)
# ---------------------------------------------------------------------------
TINYMCE_INIT = """
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
(function initTinyMCE() {
  const SELECTORS = ['textarea[data-tinymce]'];
  function launch() {
    tinymce.init({
      selector: SELECTORS.join(','),
      language: 'fa',
      directionality: 'rtl',
      plugins: 'lists link image media table code fullscreen',
      toolbar:
        'undo redo | blocks | bold italic | alignright aligncenter alignleft |'
        + ' bullist numlist | link image | code fullscreen',
      image_advtab: true,
      images_upload_url: '/admin/api/upload',
      images_upload_credentials: true,
      automatic_uploads: true,
      file_picker_types: 'image',
      content_style: "body { font-family: Vazirmatn, Tahoma, sans-serif; direction: rtl; text-align: right; font-size: 14px; }",
      height: 420,
      branding: false,
      promotion: false,
      setup: function(editor) {
        editor.on('change', function() { editor.save(); });
      }
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', launch);
  } else {
    launch();
  }
})();
</script>
"""


class TinyMCEWidget(TextArea):
    """Renders a <textarea data-tinymce> so the init script picks it up."""
    def __call__(self, field, **kwargs):
        kwargs["data-tinymce"] = "1"
        return super().__call__(field, **kwargs)


class TinyMCEField(TextAreaField):
    widget = TinyMCEWidget()


# ---------------------------------------------------------------------------
# Auth
# ---------------------------------------------------------------------------
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


# ---------------------------------------------------------------------------
# Helper: inject TinyMCE after the sqladmin form markup
# ---------------------------------------------------------------------------
class RichTextMixin:
    """Adds TinyMCE to any ModelView that has body_html / bio_html fields."""

    # sqladmin renders the page via Jinja; we hook form_include_pk to False and
    # add TinyMCE by overriding the form_widget_args to set data-tinymce attr.
    # The TINYMCE_INIT script is injected via the custom_actions_in_list trick;
    # actually the simplest reliable hook in sqladmin is form_widget_args class-level.
    pass


# ---------------------------------------------------------------------------
# Department
# ---------------------------------------------------------------------------
class DepartmentAdmin(ModelView, model=Department):
    name = "دپارتمان"
    name_plural = "دپارتمان‌ها"
    icon = "fa-solid fa-building"
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
        "body_html": {"data-tinymce": "1", "rows": 12},
        "intro": {"rows": 3},
        "image_url": {"placeholder": "/uploads/... یا URL کامل"},
    }
    form_include_pk = False


# ---------------------------------------------------------------------------
# Consultant
# ---------------------------------------------------------------------------
class ConsultantAdmin(ModelView, model=Consultant):
    name = "مشاور"
    name_plural = "مشاوران"
    icon = "fa-solid fa-user-doctor"
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
        "bio_html": {"data-tinymce": "1", "rows": 10},
        "image_url": {"placeholder": "/uploads/... یا URL کامل"},
    }


# ---------------------------------------------------------------------------
# Article
# ---------------------------------------------------------------------------
class ArticleAdmin(ModelView, model=Article):
    name = "مقاله"
    name_plural = "مقالات"
    icon = "fa-solid fa-newspaper"
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
        "body_html": {"data-tinymce": "1", "rows": 14},
        "image_url": {"placeholder": "/uploads/... یا URL کامل"},
        "excerpt": {"rows": 3},
    }


# ---------------------------------------------------------------------------
# Page (About, etc.)
# ---------------------------------------------------------------------------
class PageAdmin(ModelView, model=Page):
    name = "صفحه"
    name_plural = "صفحات"
    icon = "fa-solid fa-file-lines"
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
        "body_html": {"data-tinymce": "1", "rows": 14},
    }


# ---------------------------------------------------------------------------
# Site Settings
# ---------------------------------------------------------------------------
class SiteSettingsAdmin(ModelView, model=SiteSettings):
    name = "تنظیمات سایت"
    name_plural = "تنظیمات سایت"
    icon = "fa-solid fa-gear"
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


# ---------------------------------------------------------------------------
# Appointment Form Field builder
# ---------------------------------------------------------------------------
class AppointmentFormFieldAdmin(ModelView, model=AppointmentFormField):
    name = "فیلد فرم"
    name_plural = "فیلدهای فرم نوبت"
    icon = "fa-solid fa-list-check"
    column_list = [
        AppointmentFormField.id,
        AppointmentFormField.label,
        AppointmentFormField.field_type,
        AppointmentFormField.is_required,
        AppointmentFormField.sort_order,
        AppointmentFormField.is_active,
    ]
    column_sortable_list = [AppointmentFormField.sort_order]
    form_columns = [
        AppointmentFormField.label,
        AppointmentFormField.field_type,
        AppointmentFormField.placeholder,
        AppointmentFormField.options_json,
        AppointmentFormField.is_required,
        AppointmentFormField.sort_order,
        AppointmentFormField.is_active,
    ]
    form_widget_args = {
        "options_json": {
            "placeholder": '["گزینه ۱", "گزینه ۲"]',
            "rows": 3,
        }
    }


# ---------------------------------------------------------------------------
# Appointment inbox
# ---------------------------------------------------------------------------
class AppointmentAdmin(ModelView, model=AppointmentRequest):
    name = "درخواست نوبت"
    name_plural = "درخواست‌های نوبت"
    icon = "fa-solid fa-calendar-check"
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
        AppointmentRequest.extra_data,
        AppointmentRequest.status,
        AppointmentRequest.created_at,
    ]
    form_widget_args = {
        "notes": {"rows": 4},
        "extra_data": {"rows": 3, "readonly": True},
        "created_at": {"readonly": True},
    }


# ---------------------------------------------------------------------------
# Media upload
# ---------------------------------------------------------------------------
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
                    message = "آپلود موفق ✓"
                except Exception as exc:
                    message = str(getattr(exc, "detail", exc))
            else:
                message = "لطفاً یک فایل انتخاب کنید."

        html = f"""<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>آپلود تصویر</title>
  <style>
    body{{font-family:Tahoma,sans-serif;max-width:600px;margin:2rem auto;padding:0 1rem;background:#f8fbfc;color:#1B4283}}
    .card{{background:#fff;border:1px solid #ddd;border-radius:14px;padding:1.5rem}}
    label{{display:block;margin-bottom:.75rem;font-weight:600}}
    input[type=file]{{width:100%;padding:.5rem;border:1px solid #d7e1ea;border-radius:8px;margin-bottom:1rem}}
    button{{background:#4CC9C0;color:#122f5c;border:0;padding:.7rem 1.4rem;border-radius:8px;cursor:pointer;font-weight:700;font-size:14px}}
    .ok{{color:#0a7;margin-top:.75rem;font-weight:600}}
    .err{{color:#c00;margin-top:.75rem}}
    code{{display:block;background:#f4f4f4;padding:.5rem .75rem;border-radius:8px;margin-top:.5rem;word-break:break-all;font-size:13px;cursor:pointer}}
    code:hover{{background:#e8faf7}}
    a{{color:#1B4283;font-size:13px}}
    h2{{margin-bottom:1rem}}
    .hint{{font-size:12px;color:#888;margin-bottom:1rem}}
  </style>
</head>
<body>
  <p><a href="/admin">← بازگشت به پنل</a></p>
  <div class="card">
    <h2>آپلود تصویر</h2>
    <p class="hint">JPG, PNG, WebP, GIF — حداکثر ۵ مگابایت. پس از آپلود، آدرس را در فیلد image_url دپارتمان/مشاور/مقاله paste کنید.</p>
    <form method="post" enctype="multipart/form-data">
      <label>فایل تصویر</label>
      <input type="file" name="file" accept="image/*" required>
      <button type="submit">آپلود</button>
    </form>
    {"<p class='ok'>✓ " + message + "</p><p>آدرس:</p><code onclick=\"navigator.clipboard.writeText(this.textContent)\" title=\"کلیک برای کپی\">" + uploaded_url + "</code>" if uploaded_url else ""}
    {"<p class='err'>⚠ " + message + "</p>" if message and not uploaded_url else ""}
  </div>
</body>
</html>"""
        return HTMLResponse(html)


# ---------------------------------------------------------------------------
# Wire TinyMCE into sqladmin by customizing templates
# sqladmin supports `custom_template_dir` per view — we inject a JS snippet
# via the `form_include_pk` trick is too complex; simplest: override the
# admin-level templates with a custom `templates/admin/` directory.
# ---------------------------------------------------------------------------
ADMIN_BASE_TEMPLATE_EXTRA = TINYMCE_INIT  # used in setup_admin below


def setup_admin(app, engine):
    import os
    from pathlib import Path as _Path
    settings = get_settings()
    authentication_backend = AdminAuth(secret_key=settings["secret_key"])
    _templates_dir = str(_Path(__file__).resolve().parent / "templates" / "admin")
    admin = Admin(
        app,
        engine,
        authentication_backend=authentication_backend,
        title="پنل کلینیک دلسا",
        base_url="/admin",
        templates_dir=_templates_dir,
    )
    admin.add_view(DepartmentAdmin)
    admin.add_view(ConsultantAdmin)
    admin.add_view(ArticleAdmin)
    admin.add_view(PageAdmin)
    admin.add_view(AppointmentFormFieldAdmin)
    admin.add_view(AppointmentAdmin)
    admin.add_view(SiteSettingsAdmin)
    admin.add_view(MediaUploadView)
    return admin
