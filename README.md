# CodeIgniter 4

[![Latest Release](https://img.shields.io/github/v/release/codeigniter4/CodeIgniter4)](https://packagist.org/packages/codeigniter4/framework)
[![PHPUnit](https://github.com/codeigniter4/CodeIgniter4/actions/workflows/test-phpunit.yml/badge.svg)](https://github.com/codeigniter4/CodeIgniter4/actions/workflows/test-phpunit.yml)
[![Static Analysis](https://github.com/codeigniter4/CodeIgniter4/actions/workflows/test-phpstan.yml/badge.svg)](https://github.com/codeigniter4/CodeIgniter4/actions/workflows/test-phpstan.yml)
[![License](https://img.shields.io/github/license/codeigniter4/CodeIgniter4)](LICENSE)

CodeIgniter 4 is a modern, lightweight PHP full-stack framework focused on developer productivity, speed, and security. It provides a collection of expressive libraries, a powerful CLI, and an intuitive MVC structure that helps you ship production-ready applications quickly.

---

## Overview

- **Lightweight core** with optional packages so you only ship what you need.
- **Secure by design** thanks to CSRF protection, content security features, and built-in validation.
- **Developer friendly** CLI (`php spark`) for scaffolding, migrations, tasks, and testing.
- **Flexible architecture** that embraces PSR standards, Composer autoloading, and namespacing.

## Requirements

- PHP 8.1 or newer
- `ext-intl` and `ext-mbstring` enabled
- Recommended: `ext-curl`, `ext-json`, and database-specific extensions (`ext-mysqli`, `ext-pgsql`, etc.)

> PHP 8.1 reaches end of life on December 31, 2025. Upgrade sooner to stay supported.

## Quick Start

1. **Clone and install dependencies**
   ```bash
   git clone https://github.com/codeigniter4/CodeIgniter4.git
   cd CodeIgniter4
   composer install
   ```
2. **Bootstrap your environment**
   ```bash
   cp env .env
   php spark key:generate
   ```
   On Windows use `copy env .env` instead of `cp`.
   Update `.env` with your app name, base URL, and database credentials.
3. **Serve the application**
   ```bash
   php spark serve
   ```
   Visit `http://localhost:8080` to confirm the welcome page loads.

## Common Tasks

- `php spark make:controller Home` – scaffold a controller.
- `php spark make:migration CreateUsers` – generate a database migration.
- `php spark migrate` – run pending migrations.
- `php spark routes` – inspect the current routing table.
- `php spark help` – list available CLI commands.

Composer scripts streamline maintenance tasks:

- `composer test` – run the PHPUnit suite.
- `composer analyze` – run static analysis (PHPStan + Rector dry run).
- `composer cs` – check coding standards.
- `composer cs-fix` – automatically fix coding standards.
- `composer metrics` – generate PhpMetrics reports.

## Project Structure

- `app/` – application code (controllers, models, views, configuration).
- `public/` – web server document root containing `index.php`.
- `system/` – framework core (avoid editing directly).
- `tests/` – PHPUnit test suite.
- `writable/` – runtime storage (cache, logs, sessions, uploads).

Configure your web server (Apache/Nginx) to serve the `public/` directory to keep application files outside the public web root.

## Documentation & Guides

- Official User Guide: https://codeigniter.com/user_guide/
- In-progress Docs (latest develop): https://codeigniter4.github.io/CodeIgniter4/
- API Reference: https://codeigniter4.github.io/api/
- Community Forum: https://forum.codeigniter.com/
- Slack Community: https://codeigniterchat.slack.com/

## Contributing

We welcome bug reports, documentation updates, and pull requests from the community. Before contributing:

1. Review the [contributing guide](contributing/README.md).
2. Search existing issues to avoid duplicates.
3. Use GitHub Issues for confirmed bugs and approved enhancements; use the forum for support or feature ideas.

PRs should include tests when applicable and follow the project's coding standards (`composer cs` / `composer cs-fix`).

## Security

Report security issues via the [responsible disclosure process](SECURITY.md). Please do not open public GitHub issues for vulnerabilities.

## License

CodeIgniter 4 is open-source software released under the [MIT License](LICENSE).