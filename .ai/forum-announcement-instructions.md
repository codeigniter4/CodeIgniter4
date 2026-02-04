# Forum Announcement Instructions

## Purpose
These instructions guide the creation of forum announcements for new CodeIgniter4 releases using myBB formatting.

## Process Overview

### 1. Gather Information
Read the following source files:
- **CHANGELOG.md** - Main changelog with GitHub PR links
- **user_guide_src/source/changelogs/v{VERSION}.rst** - Detailed RST changelog with comprehensive explanations

### 2. Version Strategy
For dual releases (e.g., maintenance + major):
- **List the maintenance version first** (e.g., v4.6.5 before v4.7.0)
- Clearly explain which version users should choose based on their PHP version
- Provide separate links for each version

### 3. Announcement Structure

Use this **example** structure with myBB formatting:

```
[size=x-large][b]CodeIgniter {VERSION} & {VERSION} Released![/b][/size]

Introduction paragraph(s) - mention maintenance release first, then major release

Links to GitHub releases and changelogs

[hr]

[size=large][b]Which Version Should I Use?[/b][/size]
- Guidance for users on which version to choose

[hr]

[size=large][b]What's in CodeIgniter {MAINTENANCE_VERSION}?[/b][/size]
- Bug fixes section (if maintenance release)

[hr]

[size=large][b]Highlights & New Features ({MAJOR_VERSION})[/b][/size]
- Top features

[hr]

[size=large][b]Notable Enhancements[/b][/size]
- Bulleted list of improvements

[hr]

[size=large][b]Cache Improvements[/b][/size]
- Cache-specific updates

[hr]

[size=large][b]Database & Model Updates[/b][/size]
- Database-related changes

[hr]

[size=large][b]HTTP & Request Features[/b][/size]
- HTTP/Request improvements

[hr]

[size=large][b]Security & Quality[/b][/size]
- Security updates

[hr]

[size=large][b]Breaking Changes[/b][/size]
- Detailed breaking changes with explanations
- Include "Removed Deprecated Items" subsection

[hr]

[size=large][b]Other Notable Changes[/b][/size]
- Other miscellaneous updates

[hr]

[size=large][b]Thanks to Our Contributors[/b][/size]
- Acknowledge contributors

[hr]

Upgrade guide links
Issue reporting link
Closing message

[hr]

AI disclosure note
```

### 4. myBB Formatting Codes

Use these myBB codes:
- `[b]text[/b]` - Bold
- `[i]text[/i]` - Italic
- `[size=x-large]text[/size]` - Extra large text
- `[size=large]text[/size]` - Large text
- `[size=small]text[/size]` - Small text
- `[url=URL]text[/url]` - Links
- `[list]` - Unordered list
- `[list=1]` - Ordered list
- `[*]` - List item
- `[hr]` - Horizontal rule
- `` `code` `` - Inline code (use double backticks)

### 4a. Emoticon Escaping

myBB automatically converts emoticon patterns like `:s` (colon immediately followed by "s") into emoji. To prevent this in code blocks:

**Replace all colons with HTML entity `&#58;`**

Examples:
- `Entity::setAttributes()` → `Entity&#58;&#58;setAttributes()`
- `H:i:s` (time format) → `H&#58;i&#58;s`

This prevents emoticon conversion while displaying properly as a colon character.

### 5. Content Guidelines

**Highlights Section:**
- Emphasize PHP version requirements
- Mark experimental features as [i]Experimental[/i]
- List 3-5 most impactful features

**Enhancements:**
- Include specific config options and method names
- Use `` `code` `` for class names, methods, and config values
- Be specific about which handlers support which features

**Breaking Changes:**
- Provide detailed explanations, not just bullet points
- Include the old behavior vs. new behavior
- Mention exception type changes
- List removed deprecated items separately
- Reference specific methods and properties

**Bug Fixes (for maintenance releases):**
- Use ordered lists `[list=1]`
- Provide clear before/after descriptions

### 6. Key Points

1. **Tone:** Professional yet friendly, engaging for community (no emojis - they don't render properly in myBB)
2. **Accuracy:** Always cross-reference RST changelog for technical details
3. **Clarity:** Explain breaking changes thoroughly
4. **Contents:** Adjust sections based on the release content (e.g., skip "Cache Improvements" if no cache changes)
5. **Brevity:** For single releases, omit the "Which Version Should I Use?" section
6. **Links:** Include GitHub release links, changelogs, and upgrade guides
7. **Attribution:** Thank contributors by username
8. **Disclosure:** Add AI assistance disclosure at the end

### 7. Version Priority

For dual releases:
- Mention maintenance version (e.g., 4.6.5) **before** major version (e.g., 4.7.0)
- In "Which Version Should I Use?" section, list lower PHP version option first

### 8. Final Disclosure

Always include at the end:

```
[hr]

[size=small][i]Note: This announcement was created with the assistance of GitHub Copilot (Claude Sonnet 4.5).[/i][/size]
```

Update the agent name as necessary.

## Output File

Save the announcement as: `v{VERSION}-announcement.txt` in the repository root

## Example Workflow

```bash
# 1. Read changelogs
Read: CHANGELOG.md
Read: user_guide_src/source/changelogs/v4.7.0.rst
Read: user_guide_src/source/changelogs/v4.6.5.rst (if maintenance release)

# 2. Create announcement
Create: v4.7.0-announcement.txt

# 3. Structure content
- Introduction with both versions
- Version selection guidance
- Maintenance release details first
- Major release details
- Breaking changes (comprehensive)
- Contributors
- Upgrade links
- AI disclosure
```
