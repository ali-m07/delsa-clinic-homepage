#!/usr/bin/env python3
"""Split elementor-paste.html into Elementor-safe HTML widget parts."""

from __future__ import annotations

import copy
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "elementor-paste.html"
PARTS_DIR = ROOT / "elementor-paste-parts"
IMPORT_JSON = ROOT / "elementor-import-delsa-home.json"
MAX_BYTES = 48000


def wrap_style(css: str) -> str:
    return f"<style>\n{css}\n</style>\n"


def part_header(n: int, total: int) -> str:
    return (
        f"<!-- DELSA part {n}/{total} — ویجت HTML #{n} -->\n"
        f"<!-- هر ویجت: بلوک‌های CSS کامل و بسته — Elementor هر ویجت را جدا wrap می‌کند -->\n"
    )


def split_css(css: str, max_wrapped_size: int) -> list[str]:
    """Split only at completed CSS rules so Elementor never gets a mid-rule chunk."""
    lines = css.splitlines(keepends=True)
    chunks: list[str] = []
    current = ""
    for line in lines:
        trial = current + line
        over = len(wrap_style(trial).encode("utf-8")) > max_wrapped_size
        closed = current.rstrip().endswith("}")
        if over and current and (line.strip() == "}" or closed):
            if line.strip() == "}":
                chunks.append(trial)
                current = ""
            else:
                chunks.append(current)
                current = line
        else:
            current = trial
    if current.strip():
        chunks.append(current)
    return chunks


def split_html(html: str, max_size: int) -> list[str]:
    if len(html) <= max_size:
        return [html]
    lines = html.splitlines(keepends=True)
    chunks: list[str] = []
    current = ""
    for line in lines:
        trial = current + line
        if len(trial) > max_size and current:
            chunks.append(current)
            current = line
        else:
            current = trial
    if current:
        chunks.append(current)
    return chunks


def parse_source(text: str) -> tuple[str, str, str, str, str]:
    critical_end = text.index("</style>") + len("</style>")
    main_open = text.index("<style>\n", critical_end)
    preamble = text[:main_open].lstrip("\n")
    main_close = text.index("</style>", main_open) + len("</style>")
    main_css = text[main_open + len("<style>\n") : main_close - len("</style>")]

    html_start = main_close
    hero_open = text.index('<style id="delsa-reference-hero-overrides">', html_start)
    html_only = text[html_start:hero_open]
    last_style_end = text.rindex("</style>") + len("</style>")
    hero_style = text[hero_open:last_style_end] + "\n"
    scripts = text[last_style_end:].strip()
    return preamble, main_css, html_only, hero_style, scripts


def build_parts(text: str) -> list[str]:
    preamble, main_css, html_only, hero_style, scripts = parse_source(text)
    header_len = len(part_header(1, 1))

    first_css_limit = MAX_BYTES - len(preamble.encode("utf-8")) - header_len - 320
    css_chunks = split_css(main_css, first_css_limit)
    if len(css_chunks) > 1:
        rest = split_css("".join(css_chunks[1:]), MAX_BYTES - header_len)
        css_chunks = [css_chunks[0], *rest]
    while len(css_chunks) > 2:
        css_chunks[1] += css_chunks.pop(2)

    bodies: list[str] = [preamble + "\n\n" + wrap_style(css_chunks[0])]
    bodies.extend(wrap_style(c) for c in css_chunks[1:])
    bodies.extend(split_html(html_only, MAX_BYTES - header_len))
    bodies.append(hero_style)
    if scripts:
        bodies.append(scripts + "\n")

    total = len(bodies)
    return [part_header(i, total) + body for i, body in enumerate(bodies, start=1)]


def count_style_tags(part: str) -> tuple[int, int]:
    import re

    cleaned = re.sub(r"<!--.*?-->", "", part, flags=re.DOTALL)
    return cleaned.count("<style"), cleaned.count("</style>")
def validate_parts(parts: list[str]) -> None:
    for i, part in enumerate(parts, start=1):
        size = len(part.encode("utf-8"))
        opens, closes = count_style_tags(part)
        if size > MAX_BYTES:
            raise ValueError(f"part {i} too large: {size} bytes")
        if opens != closes:
            raise ValueError(f"part {i} unbalanced style tags: {opens} open, {closes} close")


def update_import_json(parts: list[str]) -> None:
    data = json.loads(IMPORT_JSON.read_text(encoding="utf-8"))
    html_widgets: list[dict] = []

    def collect(els: list) -> None:
        for el in els:
            if el.get("widgetType") == "html":
                html_widgets.append(el)
            if el.get("elements"):
                collect(el["elements"])

    collect(data["content"])
    template = html_widgets[0]
    column = data["content"][0]["elements"][0]

    new_widgets = []
    for i, html in enumerate(parts):
        if i < len(html_widgets):
            w = html_widgets[i]
        else:
            w = copy.deepcopy(template)
            w["id"] = f"delsa{i + 1:04d}"
        w["settings"]["html"] = html
        new_widgets.append(w)

    column["elements"] = new_widgets
    IMPORT_JSON.write_text(json.dumps(data, ensure_ascii=False, separators=(",", ":")), encoding="utf-8")


def main() -> None:
    text = SOURCE.read_text(encoding="utf-8")
    parts = build_parts(text)
    validate_parts(parts)

    PARTS_DIR.mkdir(exist_ok=True)
    for old in PARTS_DIR.glob("part*-of-*.html"):
        old.unlink()

    total = len(parts)
    for i, part in enumerate(parts, start=1):
        path = PARTS_DIR / f"part{i}-of-{total}.html"
        path.write_text(part, encoding="utf-8")
        print(
            f"{path.name}: {len(part.encode('utf-8'))} bytes, "
            f"styles {part.count('<style')} / {part.count('</style>')}"
        )

    update_import_json(parts)
    print(f"updated import json ({total} widgets)")


if __name__ == "__main__":
    main()
