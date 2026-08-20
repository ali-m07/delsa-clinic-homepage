from __future__ import annotations

from datetime import datetime
from typing import Optional

from sqlmodel import Field, SQLModel


class Department(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    slug: str = Field(index=True, unique=True)
    title: str
    nav_label: str = ""
    intro: str = ""
    body_html: str = ""
    image_url: str = ""
    sort_order: int = 0
    published: bool = True
    meta_title: str = ""
    meta_description: str = ""


class Consultant(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    slug: str = Field(index=True, unique=True)
    name: str
    role: str = ""
    bio_html: str = ""
    image_url: str = ""
    sort_order: int = 0
    published: bool = True
    department_id: Optional[int] = Field(default=None, foreign_key="department.id")


class Article(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    slug: str = Field(index=True, unique=True)
    title: str
    excerpt: str = ""
    body_html: str = ""
    image_url: str = ""
    published: bool = True
    published_at: Optional[datetime] = None


class Page(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    slug: str = Field(index=True, unique=True)
    title: str
    body_html: str = ""
    published: bool = True
    meta_title: str = ""
    meta_description: str = ""


class SiteSettings(SQLModel, table=True):
    id: Optional[int] = Field(default=1, primary_key=True)
    phone_mobile: str = "۰۹۰۲-۵۶۸۰۳۷۲"
    phone_landline: str = ""
    phone_landline2: str = "۰۲۱-۲۲۰۹۱۷۴۳"
    email: str = "info@delsaclinic.com"
    address: str = (
        "سعادت‌آباد، خیابان علامه جنوبی، نبش خیابان حق‌طلب غربی، "
        "پلاک ۸۰، ساختمان علامه، طبقه ۶، واحد ۴"
    )
    whatsapp: str = "989025680372"
    map_embed_url: str = (
        "https://www.openstreetmap.org/export/embed.html?"
        "bbox=51.365%2C35.774%2C51.385%2C35.784&layer=mapnik&marker=35.779%2C51.375"
    )
    booking_fee_note: str = "۱۰۰ هزار تومان"
    hero_image_url: str = (
        "https://delsaclinic.com/wp-content/uploads/2023/11/photo_2023-11-17_16-26-59.jpg"
    )
    logo_url: str = (
        "https://delsaclinic.com/wp-content/uploads/2021/12/DelsaClinicLogo-120x120.png"
    )


class AppointmentFormField(SQLModel, table=True):
    """A configurable field on the appointment booking form."""
    id: Optional[int] = Field(default=None, primary_key=True)
    label: str                              # label shown to visitor
    field_type: str = "text"               # text | textarea | tel | email | select | date
    placeholder: str = ""
    options_json: str = "[]"               # JSON array of strings for select fields
    is_required: bool = False
    sort_order: int = 0
    is_active: bool = True


class AppointmentRequest(SQLModel, table=True):
    id: Optional[int] = Field(default=None, primary_key=True)
    full_name: str
    phone: str
    email: str = ""
    department: str = ""
    province: str = ""
    city: str = ""
    preferred_date: str = ""
    session_type: str = ""
    notes: str = ""
    extra_data: str = ""    # JSON: values for dynamic AppointmentFormFields
    status: str = "new"
    created_at: datetime = Field(default_factory=datetime.utcnow)
