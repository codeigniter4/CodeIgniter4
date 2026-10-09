---
applyTo: "user_guide_src/**/*.rst"
---

# User guide

- Preserve the existing reStructuredText structure, terminology, directives,
  anchors, and surrounding writing style.
- Keep PHP examples compatible with PHP 8.2 and verify signatures and behavior
  against the current framework code.
- Distinguish new behavior from existing behavior and document defaults
  precisely.
- Determine the PR target branch before describing compatibility:
  `develop` follows the patch-release policy, while `4.*` follows the
  minor-release policy in the root `AGENTS.md`.
- Behavior changes, enhancements, deprecations, and important bug fixes may
  require a changelog entry.
- Changes requiring users to modify code or configuration may require an
  upgrading-guide entry.
- For an intentional compatibility break targeting `4.*`, document the old
  behavior, new behavior, affected users, reason for the change, and the
  smallest migration in both the minor changelog and upgrading guide.
- Keep code examples minimal but complete enough to run in a normal
  CodeIgniter application.
- Leave the complete Sphinx build and documentation validation to GitHub
  Actions unless the task specifically requires local documentation
  validation.
