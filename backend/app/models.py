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
