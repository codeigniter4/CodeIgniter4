---
applyTo: "system/Database/**/*.php,tests/system/Database/**/*.php"
---

# Database layer

- Consider all supported drivers: MySQLi, PostgreSQL, SQLite3, SQLSRV, and
  OCI8.
- Check identifier quoting, value binding, `NULL` behavior, transactions,
  affected rows, return types, and platform-specific SQL.
- Do not implement driver-independent behavior in only one driver.
- When changing a base database class, inspect driver overrides and their
  method signatures.
- Keep driver-independent tests separate from tests that require a live
  database.
- Use the `DatabaseLive` PHPUnit group only when a real database connection is
  required.
- Prefer focused tests for the affected base class and drivers. Leave the full
  multi-driver matrix to GitHub Actions.
