# CodeIgniter 4 Copilot Instructions

CodeIgniter 4 is a mature open-source PHP framework; production code lives
in `system/`, tests under `tests/system/`.

## Review priorities

Prioritize actionable findings involving:

- incorrect behavior or an unhandled edge case;
- backward compatibility;
- security, input validation, and context-appropriate output escaping;
- request-specific state leaking through globals, static properties, or
  shared services;
- differences between supported database drivers (MySQLi, PostgreSQL,
  SQLite3, SQLSRV, OCI8);
- missing regression tests, user-guide changes, changelog entries, or
  upgrade instructions.

Do not report formatting-only issues already enforced by PHP-CS-Fixer. Every
finding should identify a concrete failure scenario and the affected code.

## Target branch and compatibility policy

Judge compatibility against the pull request's base branch, not the
contributor's source or feature branch. If the base branch is unknown, apply
the stricter `develop` policy and report that assumption.

### Base branch `develop`

`develop` is the next patch-release line. Intentional backward-incompatible
changes are not allowed.

- Preserve public and protected APIs, documented behavior, configuration
  defaults, generated project files, exceptions, and observable side effects.
- Do not remove deprecated items or change public/protected signatures,
  visibility, or interface/abstract requirements.
- A bug or security fix may correct faulty behavior but must preserve the
  documented contract and ordinary valid usage.
- If a fix necessarily breaks compatibility, identify the impact and
  recommend retargeting the appropriate minor branch.

### Base branch `4.*`

Minor-release lines (for example `4.9`). Small, deliberate compatibility
breaks may be accepted when they materially improve the framework or
complete the documented deprecation lifecycle.

- Keep each break narrow, explicit, and justified; never an incidental
  side effect of refactoring.
- Deprecated APIs may be removed no earlier than the second subsequent
  minor release (deprecated in 4.6.x means removable in 4.8.0), with a
  supported replacement.
- Every accepted break needs tests for the new behavior, a minor-version
  changelog entry, and migration instructions in the upgrading guide.

## All base branches

- Production code must run on PHP 8.2; flag code requiring a newer PHP
  version.
- Signatures, properties, constants, default values, exceptions, side
  effects, configuration, and generated project files are all
  compatibility-sensitive.
- Dependencies should be injectable; applications must keep the ability to
  replace framework services.
- New or updated Composer dependencies require explicit justification.
- Code in `system/ThirdParty/` must not be modified.
- Do not add `declare(strict_types=1)` mechanically. Follow nearby code and
  check the `DeclareStrictTypesRector` exclusions in `rector.php`; PHP files
  use strict types unless excluded there. Do not remove or bypass an existing
  exclusion incidentally.
- New class properties need precise native types; a type change on an
  existing public or protected property follows the compatibility policy.
- Never suppress a static-analysis error or update a baseline to make a
  check pass.

## Worker Mode state safety

In FrankenPHP Worker Mode one process serves many requests. For every change
affecting application bootstrap, request handling, response sending, shutdown,
superglobals, sessions, database or cache connections, services, factories,
events, toolbar state, static state, or other request-lifecycle behavior,
inspect `system/Commands/Worker/Views/frankenphp-worker.php.tpl` even when it
is not part of the diff.

Compare traditional per-process execution with worker execution, where the
framework boots once and handles multiple requests. Classify mutable state as
process-lifetime, intentionally persistent, or request-specific. Ensure
request-specific state is refreshed or reset for every request and cannot leak
into the next request. Flag template changes that existing installations need
but that lack changelog and upgrading-guide coverage.

## Tests and documentation

- Every bug fix should include a regression test that fails without the fix.
- Do not accept weakening or removing an existing test unless the documented
  behavior changed.
- Public API, behavior, or default-value changes may require user-guide and
  changelog updates; changes requiring user action also need an
  upgrading-guide entry.
