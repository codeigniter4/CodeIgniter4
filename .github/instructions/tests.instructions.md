---
applyTo: "tests/**/*.php"
---

# PHPUnit tests

- Mirror the corresponding `system/` component and local test conventions
  where practical.
- Add the appropriate PHPUnit `Group` attribute described in
  `tests/README.md`.
- Prefer `assertSame()` and dedicated assertions over loose or generic
  assertions.
- Cover the regression or behavior through a public API where possible.
  Avoid testing private implementation details.
- Restore services, factories, environment variables, superglobals, handlers,
  locale, time-related state, and other global state modified by a test.
- Tests must not depend on execution order or state left by another test.
- Do not replace a meaningful assertion with a weaker assertion to make a test
  pass.
- After changing behavior, run the smallest relevant test file or component.
  Do not run the complete test suite by default.
