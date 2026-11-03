"""Adds the legacy anchor ids listed in the docs' .anchors.json to the headings of the built site."""

from __future__ import annotations

import html
import json
import re
import sys
from pathlib import Path

from llms import load


def main(config_path: Path) -> None:
    config = load(config_path)
    base = config_path.parent
    docs = base / config.get("docs_dir", "docs")
    site = base / config.get("site_dir", "site")
    aliases = json.loads((docs / ".anchors.json").read_text(encoding="utf-8"))

    for page, headings in aliases.items():
        target = site / (page.removesuffix(".md") + ".html")

        if not target.is_file():
            print(f"WARNING - {page} has aliases but was not built", file=sys.stderr)
            continue

        content = target.read_text(encoding="utf-8")

        for heading, ids in headings.items():
            opening = re.compile(rf'(<h[1-6]\b[^>]*\bid="{re.escape(html.escape(heading))}"[^>]*>)')
            anchors = "".join(f'<a id="{html.escape(id_)}"></a>' for id_ in ids)
            content, count = opening.subn(lambda match: match.group(1) + anchors, content, count=1)

            if count == 0:
                print(f"WARNING - {page} has no heading #{heading} for its aliases", file=sys.stderr)

        target.write_text(content, encoding="utf-8")


if __name__ == "__main__":
    main(Path(sys.argv[1] if len(sys.argv) > 1 else "mkdocs.yml"))
