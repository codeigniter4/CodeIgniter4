# Skills

This directory contains workspace agent skills for maintainers.

## Available Skills
- `forum-announcement`: Create CodeIgniter4 release forum announcements in myBB format.

## Structure
- `.github/skills/<skill-name>/SKILL.md`: Skill metadata and workflow.
- `.github/skills/<skill-name>/references/`: Optional supporting docs loaded on demand.
- `.github/skills/<skill-name>/assets/`: Optional templates and reusable files.
- `.github/skills/<skill-name>/scripts/`: Optional executables for automation.

## Usage
1. Open Copilot Chat in this workspace.
2. Invoke `/<skill-name>`.
3. Provide inputs, for example:
```
/forum-announcement 4.7.0
```

## Maintainer Notes
- Keep `name` in `SKILL.md` identical to its folder name.
- Keep the `description` keyword-rich so the skill is discoverable.
- Use references only when needed; avoid duplicating guidance between `SKILL.md` and `references/`.
