#!/usr/bin/env python3
"""Export FastAPI pages to static HTML for GitHub Pages."""

from __future__ import annotations

import os
import re
import shutil
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
BACKEND = ROOT / "backend"
DOCS = ROOT / "docs"
STATIC_SRC = BACKEND / "app" / "static"
STATIC_DST = DOCS / "static"

sys.path.insert(0, str(BACKEND))
os.environ["DATABASE_URL"] = "sqlite:///:memory:"

from fastapi.testclient import TestClient  # noqa: E402

from app.database import init_db  # noqa: E402
from app.main import app  # noqa: E402
from app.models import Department  # noqa: E402
from app.seed import seed  # noqa: E402
from sqlmodel import Session, select  # noqa: E402

from app.database import engine  # noqa: E402

# https://<user>.github.io/<repo>/
REPO = os.environ.get("GITHUB_REPOSITORY", "delsaclinic/delsa-clinic-homepage")
REPO_NAME = REPO.split("/")[-1] if "/" in REPO else REPO
BASE_HREF = os.environ.get("PAGES_BASE_URL", f"https://delsaclinic.github.io/{REPO_NAME}/").rstrip("/") + "/"


def patch_html(html: str) -> str:
    if "<base " not in html:
        html = html.replace("<head>", f'<head>\n  <base href="{BASE_HREF}">', 1)
    html = html.replace('href="/static/', f'href="{BASE_HREF}static/')
    html = html.replace('src="/static/', f'src="{BASE_HREF}static/')
    # Admin/API links stay absolute site paths on real deploy; for static demo disable admin
    html = html.replace(f'href="{BASE_HREF}admin"', 'href="#"')
    return html


def write_page(client: TestClient, url_path: str, out_path: Path) -> None:
    response = client.get(url_path, follow_redirects=True)
    if response.status_code != 200:
        raise RuntimeError(f"Failed {url_path}: {response.status_code}")
    out_path.parent.mkdir(parents=True, exist_ok=True)
    out_path.write_text(patch_html(response.text), encoding="utf-8")
    print(f"  {url_path} -> {out_path.relative_to(ROOT)}")


def main() -> None:
    print(f"Building GitHub Pages (base={BASE_HREF})")
    if DOCS.exists():
        for item in DOCS.iterdir():
            if item.name == ".gitkeep":
                continue
            if item.is_dir():
                shutil.rmtree(item)
            else:
                item.unlink()
    DOCS.mkdir(parents=True, exist_ok=True)

    init_db()
    seed()
    client = TestClient(app)

    write_page(client, "/", DOCS / "index.html")
    write_page(client, "/مشاوران", DOCS / "consultants" / "index.html")
    write_page(client, "/دپارتمان‌ها", DOCS / "departments" / "index.html")

    with Session(engine) as session:
        departments = session.exec(select(Department).order_by(Department.sort_order)).all()
        for dept in departments:
            write_page(client, f"/دپارتمان-{dept.slug}", DOCS / f"دپارتمان-{dept.slug}" / "index.html")

        from app.models import Consultant

        consultants = session.exec(select(Consultant)).all()
        for person in consultants:
            write_page(client, f"/مشاور/{person.slug}", DOCS / "مشاور" / person.slug / "index.html")

    if STATIC_SRC.exists():
        if STATIC_DST.exists():
            shutil.rmtree(STATIC_DST)
        shutil.copytree(STATIC_SRC, STATIC_DST)

    # Landing note for WP migration
    readme = DOCS / "README.md"
    readme.write_text(
        f"# Static preview\n\nGenerated site: [{BASE_HREF}]({BASE_HREF})\n\n"
        "Full CMS runs via FastAPI (`backend/`). Admin is not available in static export.\n",
        encoding="utf-8",
    )
    print("Done.")


if __name__ == "__main__":
    main()
