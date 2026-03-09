---
name: forum-announcement
description: 'Create CodeIgniter4 release forum announcements in myBB format. Use when preparing release posts, dual-version announcements (maintenance + major), changelog summaries, contributor acknowledgements, upgrade guidance, and final forum-ready text files.'
argument-hint: 'Target release version(s), e.g. "4.6.5 and 4.7.0" or "4.7.1"'
user-invocable: true
---

# Forum Announcement

Create a forum-ready CodeIgniter4 release announcement using myBB markup.

## When to Use
- Publish a new CodeIgniter4 release announcement on the forum.
- Convert changelog and RST details into a structured, readable post.
- Produce a dual-release announcement with maintenance-first ordering.

## Inputs
- One version (`4.x.y`) or two versions (`maintenance`, then `major`).

## Output Naming
- `v{VERSION}` is a placeholder and must be replaced by an actual version string.
- Single release example: `v4.7.1-announcement.txt`.
- Dual release example (`4.6.5` + `4.7.0`): use the major version filename `v4.7.0-announcement.txt`.
- Save the file at repository root.

## Procedure
1. Read `CHANGELOG.md` for release summary and PR references.
2. Read `user_guide_src/source/changelogs/v{VERSION}.rst` for each target version.
3. Determine release mode:
    - Single release: one `4.x.y` version.
    - Dual release: maintenance + major; maintenance is presented first in the post.
4. Draft the post with this structure (remove sections that do not apply):
    - Title.
    - Intro paragraphs.
    - Release links.
    - Which version to use (dual release only).
    - Maintenance release section (dual release only).
    - Highlights and new features.
    - Notable enhancements.
    - Security and quality.
    - Breaking changes.
    - Other notable changes.
    - Contributor thanks.
    - Upgrade links, issue reporting link, closing.
    - AI-assistance disclosure.
5. Apply myBB formatting and escaping rules in this file.
6. Save final content as `v{VERSION}-announcement.txt` at repository root, replacing `{VERSION}` with the actual version.

## Required Announcement Template
```text
[size=x-large][b]CodeIgniter {VERSION} Released![/b][/size]

Introduction paragraph(s)

[url=RELEASE_URL]GitHub Release[/url]
[url=CHANGELOG_URL]Changelog[/url]

[hr]

[size=large][b]Highlights & New Features[/b][/size]

[hr]

[size=large][b]Notable Enhancements[/b][/size]

[hr]

[size=large][b]Breaking Changes[/b][/size]

[hr]

[size=large][b]Thanks to Our Contributors[/b][/size]

[hr]

[url=UPGRADE_GUIDE_URL]Upgrade Guide[/url]
[url=ISSUES_URL]Report Issues[/url]

[hr]

[size=small][i]Note: This announcement was created with the assistance of GitHub Copilot (GPT-5.3-Codex).[/i][/size]
```

For dual releases, adapt the title and body to include both versions, with maintenance version first, plus a dedicated "Which Version Should I Use?" section.

## myBB Rules
- Use `[b]`, `[i]`, `[size=x-large]`, `[size=large]`, `[size=small]`, `[url=...]`, `[list]`, `[list=1]`, `[*]`, and `[hr]`.
- Use double backticks for inline code-like text when needed.
- Escape colon patterns that may trigger myBB emoticons by replacing `:` with `&#58;` in code-like snippets.

## Content Quality Rules
- Tone is professional, friendly, and community-facing.
- Do not use emojis.
- Verify details against RST changelog entries before finalizing.
- Explain breaking changes with old behavior versus new behavior.
- Include a "Removed Deprecated Items" subsection when applicable.
- For maintenance bug-fix summaries, prefer ordered lists with concise before/after statements.
- Thank contributors by GitHub username.
- Include upgrade links and issue reporting links.

## Version Guidance Rules
- Dual releases must list maintenance before major.
- The "Which Version Should I Use?" section is required for dual releases.
- In version guidance, present the lower-PHP-support option first.
- For single releases, omit the "Which Version Should I Use?" section.

## Self-Check
- Maintenance version is presented before major version.
- PHP-version guidance and upgrade links are included.
- Breaking changes are clearly explained.
- Final AI-assistance disclosure is present and should be adjusted to the actual AI model used if different from the template.
- Output filename is concrete and does not include braces.
