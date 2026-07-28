# CodeIgniter 4 — Repository Guidelines

CodeIgniter 4 is a mature open-source PHP framework. Framework production
code lives in `system/`; its tests mirror that structure under
`tests/system/`.

## Before making changes

Consult the relevant sections of:

- `contributing/pull_request.md`
- `contributing/internals.md`
- `contributing/styleguide.md`
- `tests/README.md`

Follow existing code and tests in the affected component. Search for an
existing implementation before introducing a new abstraction or convention.

## Target branch and compatibility policy

Compatibility is judged against the pull request's base branch, not the
contributor's source or feature branch. When the base branch is unknown,
apply the stricter `develop` policy.

### Base branch `develop`

`develop` is the next patch-release line. Intentional backward-incompatible
changes are not allowed.

- Preserve public and protected APIs, documented behavior, configuration
  defaults, generated project files, exceptions, and observable side effects.
- Do not remove deprecated items or change public/protected signatures,
  visibility, or interface/abstract requirements.
- A bug or security fix may correct faulty behavior but must preserve the
  documented contract and ordinary valid usage.
- If a fix necessarily breaks compatibility, target the appropriate minor
  branch instead.

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

### All base branches

- Production code must run on PHP 8.2. Do not require a newer PHP version,
  even when a newer version is installed locally.
- Signatures, properties, constants, default values, exceptions, side
  effects, configuration, and generated project files are all
  compatibility-sensitive.
- Major-scale redesigns and ecosystem-wide migration requirements belong in
  a major release, not a patch or minor release.
- Prefer the minimum useful abstraction and keep framework components as
  independent as practical.
- Dependencies should be injectable. When a framework service is used as a
  default, preserve the ability for applications to replace it.
- Do not add or update a Composer dependency unless the task explicitly
  requires it and the change is justified.
- Do not modify code in `system/ThirdParty/`.

## Coding standards

- Do not add `declare(strict_types=1)` mechanically. Follow nearby code and
  check the `DeclareStrictTypesRector` exclusions in `rector.php`; PHP files
  use strict types unless excluded there. Treat the existing exclusions as
  intentional compatibility constraints. Do not remove an exclusion without
  dedicated justification and tests.
- Every new class property must have a native type declaration. Use the
  most precise type supported by PHP 8.2; use `mixed` only when the
  property intentionally accepts unrelated types.
- Do not add or change a type on an existing public or protected property
  mechanically. Property types affect inheritance and must follow the
  target-branch compatibility policy.
- Add PHPDoc only when it contributes information that native types cannot
  express. Do not duplicate a parent or interface docblock.
- Never suppress a static-analysis error or update a baseline to make a
  check pass.

## FrankenPHP Worker Mode

In Worker Mode one process serves many requests. The entry point's source
template is `system/Commands/Worker/Views/frankenphp-worker.php.tpl`; the
`worker:install` command publishes it as `public/frankenphp-worker.php`.

- For every change affecting application bootstrap, request handling,
  response sending, shutdown, superglobals, sessions, database or cache
  connections, services, factories, events, toolbar state, static state, or
  other request-lifecycle behavior, inspect the worker entry-point template
  even when it is not part of the diff.
- Compare traditional per-process execution with worker execution, where
  the framework boots once and handles multiple requests in the same
  process.
- Classify mutable state as process-lifetime, intentionally persistent, or
  request-specific. Request-specific state must be refreshed or reset for
  every request and must not leak into the next request.
- Preserve the ordering requirements between per-request reconnection,
  framework reset, superglobal refresh, application execution, and
  post-request cleanup.
- Treat the template as the source of truth. Do not edit a generated
  `public/frankenphp-worker.php` in place.
- When a release changes the template in a way existing installations must
  receive, add an upgrading instruction telling Worker Mode users to
  republish it with `php spark worker:install --force`.

## Tests

- Every bug fix should include a regression test that fails without the
  fix.
- Test failure paths, exceptions, boundary conditions, and state cleanup,
  not only the happy path.
- Prefer strict and dedicated PHPUnit assertions as described in the
  project style guide.
- Do not weaken or remove an existing test unless the documented behavior
  or specification changed.

## Documentation

- Public API, behavior, message, or default-value changes may require an
  update to the user guide and changelog.
- Changes requiring user action, including configuration changes, may also
  require an upgrading-guide entry.
- For a `4.*` target, every intentional compatibility break must be
  documented in both the minor-version changelog and its upgrading guide.

## Focused validation

GitHub Actions is the authoritative source for full validation. Do not
reproduce the complete CI matrix locally.

- Run only the narrowest PHPUnit test file or component that covers a code
  change, for example
  `vendor/bin/phpunit tests/system/<Component>/<Class>Test.php`.
- Run a file-scoped analysis or formatting check only when it is quick and
  directly relevant.
- Do not run the complete PHPUnit suite, `composer phpstan:check`,
  `composer cs`, Psalm, Structarmed, or a repository-wide Rector analysis
  by default.
- Report which focused checks were executed and which validation was left
  to CI.
