# LaraDate

A Laravel dating application built with DDEV.

## Prerequisites

- [Docker](https://www.docker.com/get-started/)
- [DDEV](https://docs.ddev.com/en/stable/#installation) (v1.24+)

## Quick Start

```bash
# Clone the repository
git clone git@github.com:moreishi/laravel-dating.git
cd laravel-dating

# Start DDEV
ddev start

# Install dependencies
ddev composer install

# Set up the database
ddev php artisan migrate --seed

# Build frontend assets
ddev npm install
ddev npm run build
```

## Access the Application

| URL | Description |
|-----|-------------|
| http://opencode-laravel-dating.ddev.site:33000 | Main site |
| https://opencode-laravel-dating.ddev.site:33001 | HTTPS |

## Default Accounts

| Email | Password | Role |
|-------|----------|------|
| test@example.com | password | Test user |
| alice@example.com | password | Sample user |
| bob@example.com | password | Sample user |

## Common Commands

```bash
# Start / stop the environment
ddev start
ddev stop

# Run tests
ddev php artisan test

# Run a single test file
ddev php artisan test tests/Feature/ConversationTest.php

# Clear and rebuild caches
ddev php artisan optimize:clear
ddev php artisan view:clear

# Run Laravel Pint (code style)
ddev exec ./vendor/bin/pint

# Rebuild frontend assets
ddev npm run build

# Watch for frontend changes
ddev npm run dev

# Database
ddev php artisan migrate:fresh --seed
ddev php artisan db:show

# SSH into the web container
ddev ssh

# View project info (URLs, ports, services)
ddev describe

# View logs
ddev logs

# Restart (useful if PHP-FPM socket errors occur)
ddev restart
```

## Tech Stack

- **Framework:** Laravel 13 / PHP 8.4
- **Database:** MariaDB 10.11
- **Frontend:** Blade + Tailwind CSS + Alpine.js + HTMX
- **Data Transfer:** Spatie laravel-data
- **Testing:** Pest / PHPUnit
- **Local Dev:** DDEV (nginx-fpm)

## Project Structure

```
app/
├── Actions/          # Action classes (StartConversation, SendMessage)
├── Data/             # Spatie DTOs
├── Enums/            # PHP enums (Gender)
├── Exceptions/       # Custom exceptions
├── Http/Controllers/ # Controllers
├── Interfaces/       # Repository interfaces
├── Models/           # Eloquent models
├── Observers/        # Model observers
├── Policies/         # Authorization policies
├── Providers/        # Service providers
├── Repositories/     # Repository implementations
└── Services/         # Service layer
```

## Architecture

- **Repository Pattern** — Interfaces bound via `RepositoryServiceProvider`
- **Service Layer** — Business logic orchestration
- **Action Classes** — Single-responsibility operations
- **DTOs** — Spatie `laravel-data` for typed data transfer
- **Policies** — Authorization on conversations and messages
- **Observers** — Automatic `updated_at` touching on messages
