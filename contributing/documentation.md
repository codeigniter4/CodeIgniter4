# Writing CodeIgniter Documentation

CodeIgniter's user guide is written in Markdown, in the **docs/** folder, and built with [MkDocs](https://www.mkdocs.org/) and the [Material](https://squidfunnel.github.io/mkdocs-material/) theme.
The focus is on readability and user friendliness.
While the pages can be quite technical, we always write for humans!

- [Tools Required](#tools-required)
- [Pages and the Navigation](#pages-and-the-navigation)
- [Page and Section Headings](#page-and-section-headings)
- [Links](#links)
- [Admonitions](#admonitions)
- [Text Decoration](#text-decoration)
- [Code Blocks](#code-blocks)
- [Line Breaks and Whitespace](#line-breaks-and-whitespace)

## Tools Required

To preview the rendered site, you need Python 3.11 or newer. Install MkDocs and its dependencies into a virtual environment:

```console
python3 -m venv venv
venv/bin/pip install -r docs/requirements.txt
```

Then serve the user guide at <http://127.0.0.1:8000>, which reloads when you save a page:

```console
venv/bin/mkdocs serve
```

GitHub Actions builds the user guide for every pull request that changes **docs/**, with `mkdocs build --strict`.
A broken link, a missing page or an unknown anchor fails the build, so you can leave the full validation to it unless you are changing the build itself.

## Pages and the Navigation

Each page is a Markdown file in **docs/**, such as **docs/libraries/pagination.md**.
The navigation is the file **docs/.nav.yml**.

When you add a page, run the script that adds it to the navigation:

```console
php admin/update-docs-nav.php
```

The script places the page in the section of its folder, in the order of the links in that folder's **index.md**.
Add the link to the **index.md** first, and edit **docs/.nav.yml** by hand only to change the order or the title of an entry.
The pull request check runs `php admin/update-docs-nav.php --check`, which fails when a page is missing from the navigation or an entry has no file.

## Page and Section Headings

Headings provide the order and the sections of a page, and they build the table of contents on the right of the page.
Use one `#` heading for the page title, then `##` for sections and `###` for subsections, without skipping a level:

```markdown
# Page Title

## Major Section

### Subsection

#### SubSubSection
```

Do not add a table of contents yourself. The theme builds it from the headings, down to the third level.

## Links

### To a Section

Every heading gets an anchor from its text, in lowercase with hyphens in place of spaces and punctuation.
The heading `## Result Rows` has the anchor `#result-rows`.

To link to a section of another page:

```markdown
See [Result Rows](../database/results.md#result-rows) for how to read them.
```

To link to a section of the same page:

```markdown
See [Result Rows](#result-rows)
```

### To a Page

Link to the Markdown file with a path relative to the current page:

```markdown
See the [Session](../libraries/sessions.md) library
```

### To a URL

```markdown
[CodeIgniter 4 framework](https://github.com/codeigniter4/framework)
```

### To a Function or a Method

Write the name as code, or link it to the page that describes it:

```markdown
See [dot_array_search()](../helpers/array_helper.md)

See `CodeIgniter\HTTP\Response::setCookie()`
```

## Admonitions

Use an admonition for a note, a warning or a version badge.
The body is indented by four spaces.

```markdown
!!! note "Note"
    You must call this method first to generate correct pagination links.

!!! important "Important"
    Do not edit the files in the **system** folder.

!!! warning "Warning"
    This setting disables the CSRF protection.
```

The types in use are `note`, `important`, `warning`, `danger`, `success` and `tip`.

### New Feature

Mark a feature that is new in a version with a `success` admonition that has the title `Available from version`, followed by the version:

```markdown
!!! success "Available from version 4.3.0"
```

### Deprecated

Mark a deprecation with a `danger` admonition, and say what to use instead:

```markdown
!!! danger "Deprecated since version 4.3.0"
    Use `CodeIgniter\Database\BaseBuilder::setData()` instead.
```

## Text Decoration

As a general rule, we use `**` for in-line file paths, and a single backtick for source code.

E.g.:

```markdown
Open the **app/Config/Filters.php** file and update the `$methods` property like the following:
```

## Code Blocks

Fence a code block with three backticks and the name of its language:

````markdown
```php
<?php

$pager = service('pager');
```
````

A PHP sample that starts with `<?php` and parses is checked by `composer cs`, and `composer cs-fix` rewrites it in place.
To skip a sample, such as CodeIgniter 3 code, put `<!-- lint-ignore -->` on the line above its fence.

### CLI Command

Use the `console` language for a command that is run in a terminal:

````markdown
```console
php spark migrate
```
````

Other languages in use are `html`, `apache`, `json` and `text`.

## Line Breaks and Whitespace

Python-Markdown, the parser behind MkDocs, is stricter than the Markdown on GitHub:

- Indent nested lists and the body of an admonition by four spaces.
- A line that ends with two spaces is a hard line break. Your editor must not trim them, and **.editorconfig** already turns trimming off for **docs/**.
- Leave a blank line before a list, a code fence and a table.
