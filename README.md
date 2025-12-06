# Environment Manager

A compact Laravel 12 + Filament 4 application for managing and inspecting environment configuration. It ships with sensible defaults and a developer-friendly setup.

<p align="center">
  <a href="https://img.shields.io/badge/PHP-8.4-777BB3?logo=php"> <img alt="PHP" src="https://img.shields.io/badge/PHP-8.4-777BB3?logo=php"> </a>
  <a href="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel"> <img alt="Laravel" src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel"> </a>
  <a href="https://img.shields.io/badge/Filament-4-2B2E3A"> <img alt="Filament" src="https://img.shields.io/badge/Filament-4-2B2E3A"> </a>
  <a href="https://img.shields.io/badge/Tests-Pest%204-6C5CE7"> <img alt="Pest" src="https://img.shields.io/badge/Tests-Pest%204-6C5CE7"> </a>
  <a href="https://img.shields.io/badge/License-MIT-green"> <img alt="License" src="https://img.shields.io/badge/License-MIT-green"> </a>
</p>

## Introduction

**--> This project is currently work in progress <--**

Environment Manager is a Laravel application focused on clarity and maintainability of environment-based configuration. It provides a clean baseline for local development and production-ready defaults.

## Installation

### Run by docker

#### All in one
`
docker run --name=<name> -p <port>:80 nodusit/laravel-env-manager:nginx-latest
`

#### Compose (production, separate Nginx; repo-independent)

For production, use the PHP-FPM only image and run Nginx separately.

Use the in-repo Compose setup instead of an embedded snippet:
- Compose file: `.docker/compose/docker-compose.yml`
- Nginx config used by the Compose setup: `.docker/compose/default.conf`

Quick start:

```
docker compose -f ./.docker/compose/docker-compose.yml up -d
```

Then initialize the app (generate key, run migrations):

```
docker compose -f ./.docker/compose/docker-compose.yml exec app php artisan key:generate
docker compose -f ./.docker/compose/docker-compose.yml exec app php artisan migrate
```

Essential environment variables (examples):

```
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=mariadb
DB_HOST=db
DB_PORT=3306
DB_DATABASE=env_manager
DB_USERNAME=env
DB_PASSWORD=secret
```

The application is exposed on http://localhost:8080.

### Run manually
- Clone the repository
- Run `composer setup`
- Run `php artisan serve` or connect with your favourite webserver


## Configuration

All configuration lives in the `config/` directory and is driven by environment variables. Below is an English summary of the ENV variables effectively used by this project’s config files and their defaults.

### Environment variables overview

| Variable | Description | Default |
|---|---|---|
| APP_NAME | Application name; also used for the session cookie name | Environment-Manager |
| APP_ENV | Application environment | production |
| APP_DEBUG | Debug mode | false |
| APP_URL | Base application URL | http://localhost:80 |
| APP_TIMEZONE | Default timezone | Europe/Berlin |
| APP_LOCALE | Default locale | de |
| APP_FALLBACK_LOCALE | Fallback locale | en |
| APP_KEY | Encryption key | — (none) |
| APP_PREVIOUS_KEYS | Comma-separated list of previous app keys | '' (empty) |
| LOG_LEVEL | Log level for `daily` and `stderr` channels | debug |
| DB_CONNECTION | Default database connection | sqlite |
| DB_URL | DSN/URL for database connection | — (none) |
| DB_DATABASE | Database name/path | sqlite: storage/database.sqlite; otherwise: laravel |
| DB_FOREIGN_KEYS | SQLite: enforce foreign keys | true |
| DB_HOST | DB host (MySQL/MariaDB/PostgreSQL) | 127.0.0.1 |
| DB_PORT | DB port (MySQL/MariaDB) | 3306 |
| DB_PORT | DB port (PostgreSQL) | 5432 |
| DB_USERNAME | DB username | root |
| DB_PASSWORD | DB password | '' (empty) |
| DB_SOCKET | Unix socket (MySQL/MariaDB) | '' (empty) |
| DB_CHARSET | Charset (MySQL/MariaDB/PostgreSQL) | utf8mb4 (PostgreSQL: utf8) |
| DB_COLLATION | Collation (MySQL/MariaDB) | utf8mb4_unicode_ci |
| MYSQL_ATTR_SSL_CA | Path to SSL CA (MySQL/MariaDB) | — (none) |
| MAIL_SCHEME | Mail scheme (e.g., tls) | — (none) |
| MAIL_URL | SMTP URL | — (none) |
| MAIL_HOST | SMTP host | 127.0.0.1 |
| MAIL_PORT | SMTP port | 2525 |
| MAIL_USERNAME | SMTP username | — (none) |
| MAIL_PASSWORD | SMTP password | — (none) |
| MAIL_EHLO_DOMAIN | EHLO/HELO domain | Host derived from APP_URL if not set |
| MAIL_FROM_ADDRESS | Global sender address | hello@example.com |
| MAIL_FROM_NAME | Global sender name | Example |

Notes:
- Some defaults depend on the selected driver (e.g., `DB_DATABASE`). Values above reflect what’s defined in `config/`.
- Derived defaults (e.g., `MAIL_EHLO_DOMAIN`) will be computed from the current app configuration if not set.

## License

This project is open-sourced software licensed under the MIT license.
