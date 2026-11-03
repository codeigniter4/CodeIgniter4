"""Writes llms.txt, llms-full.txt and each page's Markdown beside its HTML in the built site."""

from __future__ import annotations

import re
import sys
from pathlib import Path

import yaml


class Loader(yaml.SafeLoader):
    pass


Loader.add_multi_constructor("!", lambda loader, suffix, node: None)


def load(path: Path) -> dict:
    return yaml.load(path.read_text(encoding="utf-8"), Loader=Loader) or {}


def title_of(markdown: Path) -> str:
    fenced = False

    for line in markdown.read_text(encoding="utf-8").splitlines():
        if line.startswith("```"):
            fenced = not fenced
        elif not fenced and (match := re.match(r"# (.+?)\s*#*\s*$", line)):
            return match.group(1)

    return markdown.stem


def index_lines(items: list, docs: Path, depth: int = 0) -> list[str]:
    indent = "    " * max(depth - 1, 0)
    lines: list[str] = []

    for item in items:
        title, target = next(iter(item.items())) if isinstance(item, dict) else (None, item)

        if isinstance(target, list):
            lines += ["", f"## {title}", ""] if depth == 0 else [f"{indent}- {title}"]
            lines += index_lines(target, docs, depth + 1)
        elif isinstance(target, str) and target.endswith(".md"):
            lines.append(f"{indent}- [{title or title_of(docs / target)}]({target})")

    return lines


def nav_pages(items: list) -> list[str]:
    pages: list[str] = []

    for item in items:
        target = next(iter(item.values())) if isinstance(item, dict) else item

        if isinstance(target, list):
            pages += nav_pages(target)
        elif isinstance(target, str) and target.endswith(".md"):
            pages.append(target)

    return pages


def main(config_path: Path) -> None:
    config = load(config_path)
    base = config_path.parent
    nav = config.get("nav") or load(base / config["INHERIT"]).get("nav", [])
    docs = base / config.get("docs_dir", "docs")
    site = base / config.get("site_dir", "site")

    in_nav = nav_pages(nav)
    outside_nav = sorted(
        path.relative_to(docs).as_posix() for path in docs.rglob("*.md") if path.relative_to(docs).as_posix() not in in_nav
    )
    pages = in_nav + outside_nav

    for page in pages:
        (site / page).write_text((docs / page).read_text(encoding="utf-8"), encoding="utf-8")

    index = [f"# {config['site_name']}", "", f"> {config['site_description']}", "", *index_lines(nav, docs)]

    if outside_nav:
        index += ["", "## Other pages", ""]
        index += [f"- [{title_of(docs / page)}]({page})" for page in outside_nav]

    (site / "llms.txt").write_text("\n".join(index) + "\n", encoding="utf-8")
    (site / "llms-full.txt").write_text(
        "\n\n".join((docs / page).read_text(encoding="utf-8").strip() for page in pages) + "\n",
        encoding="utf-8",
    )


if __name__ == "__main__":
    main(Path(sys.argv[1] if len(sys.argv) > 1 else "mkdocs.yml"))
