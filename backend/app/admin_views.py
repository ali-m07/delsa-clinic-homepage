from sqladmin import Admin, ModelView
from sqladmin.authentication import AuthenticationBackend
from starlette.requests import Request

from app.config import get_settings
from app.models import Article, Consultant, Department


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
    return admin
