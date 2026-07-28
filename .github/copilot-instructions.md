# Copilot Code Review Instructions

Repository-wide conventions, the target-branch compatibility policy, coding
standards, and FrankenPHP Worker Mode requirements are defined in the root
`AGENTS.md`. Apply that policy in every review; this file covers review
behavior only.

## Review priorities

Prioritize actionable findings involving:

- incorrect behavior or an unhandled edge case;
- backward compatibility;
- security, input validation, and context-appropriate output escaping;
- request-specific state leaking through globals, static properties, or
  shared services, including under Worker Mode where one process serves
  many requests;
- differences between supported database drivers (MySQLi, PostgreSQL,
  SQLite3, SQLSRV, OCI8);
- missing regression tests, user-guide changes, changelog entries, or
  upgrade instructions.

## Review behavior

- Determine the PR base branch and apply the compatibility policy from
  `AGENTS.md`. If the base branch cannot be determined, assume `develop`
  and state that assumption.
- Do not report formatting-only issues already enforced by PHP-CS-Fixer.
- Every finding should identify a concrete failure scenario and the
  affected code.
