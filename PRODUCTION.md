# LaraDate — Docker Production Guide

## Overview

LaraDate is a Laravel 13 dating application with profile browsing, conversations, and real-time messaging. This guide covers deploying to production using Docker.

Live at: **https://laradate.belzacia.com**

### Key Features

- User registration & authentication (Breeze Blade)
- Profile management (age, bio, gender)
- Profile browsing with pagination
- One-on-one conversations
- Real-time messaging via HTMX
- Rate-limited endpoints
- Authorization policies

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Framework** | Laravel 13.x |
| **PHP** | 8.4 |
| **Database** | MariaDB 10.11 |
| **Web Server** | nginx (in container) |
| **Frontend** | Blade + Tailwind CSS 3.x + Alpine.js 3.x + HTMX 2.x |
| **Asset Bundler** | Vite 8.x |
| **Data Layer** | Spatie laravel-data 4.x (DTOs) |
| **Queue** | Database driver |
| **Cache** | Database driver |
| **Session** | Database driver |
| **Container Base** | `serversideup/php:8.4-fpm-nginx` |

---

## Architecture

```
┌─────────────────────────────────────┐
│           Routes (web.php)          │
├─────────────────────────────────────┤
│         Controllers (thin)          │
├─────────────────────────────────────┤
│   Actions (single-responsibility)   │
├─────────────────────────────────────┤
│    Services (orchestration layer)   │
├─────────────────────────────────────┤
│   Repositories (data access)        │
├─────────────────────────────────────┤
│    Models (Eloquent)                │
└─────────────────────────────────────┘
```

- **Repository Pattern** — Interfaces bound via `RepositoryServiceProvider`
- **Action Classes** — `StartConversationAction`, `SendMessageAction`
- **DTOs** — Spatie `laravel-data` for typed input validation
- **Policies** — `ConversationPolicy`, `MessagePolicy`
- **Observers** — `MessageObserver` auto-touches conversation `updated_at`
- **No API Resources** — Views render directly from controllers

### Data Flow

```
User → Route → Controller → Policy → Action → Service → Repository → Model → DB
  ← View ← Controller ← Policy ← Model ← Repository ← Service ← Action ←─────────┘
```

---

## Docker Deployment

### Prerequisites

- Docker 24+ and Docker Compose v2
- Git
- A server (VPS, cloud instance, or bare metal) with Docker installed
- A domain name pointing to your server (for HTTPS)

### Quick Start

```bash
# 1. Clone the repository
git clone https://github.com/moreishi/laravel-dating.git
cd laradate

# 2. Copy Docker environment template
cp .env.docker .env

# 3. Generate app key
docker compose -f docker-compose.prod.yml run --rm app php artisan key:generate --show

# 4. Paste the key into .env as APP_KEY=base64:...

# 5. Start all services
docker compose -f docker-compose.prod.yml up -d --build

# 6. Run migrations with seed data
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --force

# 7. Verify
curl http://localhost:8081/healthcheck
```

### Project Docker Files

| File | Purpose |
|------|---------|
| `Dockerfile` | Multi-stage build: Node.js for assets → Composer for deps → PHP 8.4 + nginx |
| `docker-compose.prod.yml` | Full stack: app, queue, scheduler, MariaDB |
| `.dockerignore` | Excludes dev files from the build context |
| `.env.docker` | Docker environment template |
| `nginx.conf` | Base nginx configuration |

### Dockerfile Breakdown

```
┌─────────────────────────────────────────────────────┐
│ Stage 1: node:22-alpine (assets)                    │
│   npm install → npm run build                       │
├─────────────────────────────────────────────────────┤
│ Stage 2: composer:2 (dependencies)                  │
│   composer install --no-dev --optimize              │
│   (fakerphp/faker in require for seeding)           │
├─────────────────────────────────────────────────────┤
│ Stage 3: serversideup/php:8.4-fpm-nginx             │
│   USER root → install-php-extensions               │
│   Copy vendor + source + built assets               │
│   chown storage to www-data                         │
│   php artisan view:cache + route:cache + event:cache│
│   HEALTHCHECK localhost:8080/healthcheck            │
└─────────────────────────────────────────────────────┘
```

### Services

| Service | Container | Purpose |
|---------|-----------|---------|
| `app` | `laradate-app` | nginx + PHP-FPM, serves the application on port 8081 |
| `queue` | `laradate-queue` | Background job processor |
| `scheduler` | `laradate-scheduler` | Laravel task scheduler (`schedule:work`) |
| `db` | `laradate-db` | MariaDB 10.11 database |

### Port Mapping

| Host Port | Container Port | Service |
|-----------|---------------|---------|
| `8081` | `80` | nginx (app) |

The app container exposes nginx on port `80` internally. The host maps it to `8081`. A host-level nginx reverse proxy (see HTTPS section) forwards `laradate.belzacia.com:443` → `127.0.0.1:8081`.

---

## Environment Configuration

### `.env` for Docker Production

```bash
# ── Application ──
APP_NAME=LaraDate
APP_ENV=production
APP_KEY=base64:tLbVM2W6VMdxNoMFM6pewsEkGaa9GadpIHuniA4uMHo=
APP_DEBUG=false
APP_URL=https://laradate.belzacia.com

# ── Database ──
DB_DATABASE=laradate
DB_USERNAME=laradate
DB_PASSWORD=your-strong-db-password

# ── Docker ──
APP_PORT=8081
DB_ROOT_PASSWORD=your-strong-root-password

# ── Session ──
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true

# ── Cache & Queue ──
CACHE_STORE=database
QUEUE_CONNECTION=database

# ── Mail ──
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# ── Logging ──
LOG_LEVEL=warning

# ── Filesystem ──
FILESYSTEM_DISK=local
```

### Variable Reference

| Variable | Default | Notes |
|----------|---------|-------|
| `APP_NAME` | `LaraDate` | Displayed in page titles |
| `APP_ENV` | `production` | Never change in production |
| `APP_DEBUG` | `false` | Never `true` in production |
| `APP_KEY` | *(generated)* | Required for encryption |
| `APP_URL` | `http://localhost` | Must match HTTPS domain for asset URLs |
| `APP_PORT` | `8081` | Host port for nginx container |
| `DB_PASSWORD` | *(required)* | Strong password for app user |
| `DB_ROOT_PASSWORD` | *(required)* | Strong password for root |
| `SESSION_SECURE_COOKIE` | `true` | Must be `true` with HTTPS |
| `LOG_LEVEL` | `warning` | `debug` for troubleshooting |

### Important Configuration Notes

- **Trusted Proxies**: `bootstrap/app.php` includes `$middleware->trustProxies(at: '*')` so that `asset()` and `url()` helpers generate HTTPS URLs when behind a TLS-terminating reverse proxy.
- **Faker in Production**: `fakerphp/faker` is in the `require` section of `composer.json` (not `require-dev`) so that seeders can run in production environments.

---

## Deployment Workflow

### Manual Deploy

```bash
# Pull latest code
git pull origin main

# Rebuild and restart containers
docker compose -f docker-compose.prod.yml up -d --build

# Run migrations
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Seed database (optional)
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --force

# Rebuild caches
docker compose -f docker-compose.prod.yml exec -u root app php artisan optimize
```

### Automated Deploy (GitHub Actions)

The repository includes `.github/workflows/deploy.yml`. To use it:

1. **Add repository secrets** in GitHub → Settings → Secrets:

| Secret | Value |
|--------|-------|
| `SSH_PRIVATE_KEY` | Your server SSH private key |
| `SERVER_HOST` | Server IP or hostname (`62.169.22.255`) |
| `SERVER_USER` | SSH username (`root`) |
| `DEPLOY_PATH` | Path to the app on the server (`/var/www/laradate`) |

2. **Push to `main`** — the workflow builds, tests, and deploys automatically.

### Zero-Downtime Deploy

```bash
# 1. Pull new image
docker compose -f docker-compose.prod.yml pull

# 2. Start new containers (old ones keep serving)
docker compose -f docker-compose.prod.yml up -d --no-deps app

# 3. Run migrations
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# 4. Restart queue and scheduler
docker compose -f docker-compose.prod.yml restart queue scheduler

# 5. Clean up old images
docker image prune -f
```

---

## HTTPS with Host nginx + Let's Encrypt

This project does **not** use an in-container reverse proxy (Caddy/Traefik). Instead, the host machine runs nginx with Let's Encrypt SSL, proxying requests to the Docker container.

### Host nginx Configuration

The production server (`62.169.22.255`) has nginx installed on the host, configured at `/etc/nginx/sites-enabled/laradate`:

```nginx
server {
    listen 80;
    server_name laradate.belzacia.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name laradate.belzacia.com;

    ssl_certificate /etc/letsencrypt/live/laradate.belzacia.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/laradate.belzacia.com/privkey.pem;

    location / {
        proxy_pass http://127.0.0.1:8081;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Container nginx Configuration

The container's nginx configuration is at `conf.d/default.conf`, which is a symlink to `sites-available/ssl-off`:

```
server {
    listen 80;
    server_name _;
    root /var/www/html/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Setting Up Host nginx

```bash
# Install nginx
apt update && apt install -y nginx certbot python3-certbot-nginx

# Create site config
nano /etc/nginx/sites-available/laradate

# Enable site
ln -s /etc/nginx/sites-available/laradate /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx

# Obtain SSL certificate
certbot --nginx -d laradate.belzacia.com

# Auto-renewal is configured by certbot via systemd timer
certbot renew --dry-run
```

---

## Database Management

### Migrations (7 total)

| Migration | Purpose |
|-----------|---------|
| `create_users_table` | Default Laravel users |
| `create_cache_table` | Cache store |
| `create_jobs_table` | Queue jobs |
| `add_profile_fields_to_users_table` | `age`, `bio`, `gender` |
| `create_conversations_table` | Conversations |
| `create_conversation_user_table` | Pivot with `last_read_at` |
| `create_messages_table` | Messages with FKs |

### Commands

```bash
# Run pending migrations
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Rollback last batch
docker compose -f docker-compose.prod.yml exec app php artisan migrate:rollback --step=1 --force

# View migration status
docker compose -f docker-compose.prod.yml exec app php artisan migrate:status

# Seed with pre-configured accounts
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --force

# View database stats
docker compose -f docker-compose.prod.yml exec app php artisan db:show
```

### Backup & Restore

```bash
# Backup database
docker compose -f docker-compose.prod.yml exec db \
  mysqldump --single-transaction --routines --events \
  -u laradate -p"${DB_PASSWORD}" laradate \
  | gzip > backup-$(date +%Y%m%d-%H%M%S).sql.gz

# Restore database
gunzip -c backup-20260711.sql.gz | \
  docker compose -f docker-compose.prod.yml exec -T db \
  mysql -u laradate -p"${DB_PASSWORD}" laradate

# Automated backup script
cat > /usr/local/bin/laradate-backup.sh << 'SCRIPT'
#!/bin/bash
BACKUP_DIR=/var/backups/laradate
mkdir -p "$BACKUP_DIR"
DATE=$(date +%Y%m%d-%H%M%S)
cd /path/to/laradate
source .env
docker compose -f docker-compose.prod.yml exec db \
  mysqldump --single-transaction --routines --events \
  -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" \
  | gzip > "$BACKUP_DIR/laradate-db-$DATE.sql.gz"
find "$BACKUP_DIR" -name "*.sql.gz" -mtime +30 -delete
SCRIPT
chmod +x /usr/local/bin/laradate-backup.sh

# Add to crontab: 0 2 * * * /usr/local/bin/laradate-backup.sh
```

---

## Monitoring & Maintenance

### Health Check

```bash
# Check app health
curl -s -o /dev/null -w "%{http_code}" https://laradate.belzacia.com/healthcheck
# Expected: 200

# Check container health
docker compose -f docker-compose.prod.yml ps
docker inspect --format='{{.State.Health.Status}}' laradate-db
```

### Logs

```bash
# All services
docker compose -f docker-compose.prod.yml logs -f

# Specific service
docker compose -f docker-compose.prod.yml logs -f app
docker compose -f docker-compose.prod.yml logs -f queue
docker compose -f docker-compose.prod.yml logs -f db

# Application logs
docker compose -f docker-compose.prod.yml exec app tail -f storage/logs/laravel.log

# Last 100 lines
docker compose -f docker-compose.prod.yml logs --tail=100 app
```

### Common Commands

```bash
# Clear all caches
docker compose -f docker-compose.prod.yml exec app php artisan optimize:clear

# Rebuild all caches
docker compose -f docker-compose.prod.yml exec -u root app php artisan optimize

# Run tests
docker compose -f docker-compose.prod.yml exec app php artisan test

# Queue status
docker compose -f docker-compose.prod.yml exec app php artisan queue:monitor

# Maintenance mode
docker compose -f docker-compose.prod.yml exec app php artisan down
docker compose -f docker-compose.prod.yml exec app php artisan up

# SSH into a container
docker compose -f docker-compose.prod.yml exec app bash
docker compose -f docker-compose.prod.yml exec db bash

# View resource usage
docker stats laradate-app laradate-queue laradate-db

# Restart a single service
docker compose -f docker-compose.prod.yml restart queue
```

---

## Security Checklist

Run through this before going live:

- [x] **`APP_ENV=production`** — set in `.env`
- [x] **`APP_DEBUG=false`** — prevents stack trace leaks
- [x] **`APP_KEY` generated** — `php artisan key:generate --show`
- [x] **`SESSION_SECURE_COOKIE=true`** — HTTPS only
- [x] **Strong database passwords** — both `DB_PASSWORD` and `DB_ROOT_PASSWORD`
- [x] **Rate limiting active** — `throttle:5,1` on conversations, `throttle:10,1` on messages
- [x] **CSRF protection** — all POST forms include `@csrf`
- [x] **HTTPS enabled** — host nginx with Let's Encrypt
- [x] **Trusted proxies configured** — `bootstrap/app.php` trusts `*`
- [x] **`.env` not in container** — excluded via `.dockerignore`
- [x] **Storage volume mounted** — `storage:/var/www/html/storage`
- [x] **Non-root container user** — `serversideup/php` runs as UID 9999; PHP extensions installed as root then dropped back
- [x] **Health check configured** — `HEALTHCHECK` in Dockerfile
- [x] **Queue worker running** — `queue` service auto-restarts
- [x] **Database backup automated** — cron job or external service
- [x] **SSL/TLS active** — Let's Encrypt on host
- [ ] **Fail2ban on host** — protect SSH and exposed ports
- [x] **Firewall rules** — only ports 80, 443, and SSH open

---

## Troubleshooting

### Common Issues

| Symptom | Cause | Fix |
|---------|-------|-----|
| `APP_KEY` error | Missing key | `docker compose run --rm app php artisan key:generate --show` |
| 419 page expired | CSRF mismatch | Clear browser cookies, regenerate `APP_KEY` |
| 500 error | Cache outdated | `docker compose exec app php artisan optimize:clear` |
| Login redirect loop | `SESSION_SECURE_COOKIE` mismatch | Set `true` with HTTPS, `false` for HTTP |
| Class not found | Stale autoload | `docker compose exec app composer dump-autoload` |
| Route not found | Route cache stale | `docker compose exec app php artisan route:clear` |
| Blank page | PHP error | `docker compose logs app` |
| Messages not sending | Queue not running | `docker compose restart queue` |
| DB connection refused | Container not ready | `docker compose ps` — check `db` health |
| Vite manifest missing | Assets not built | Rebuild: `docker compose up -d --build` |
| Permission denied | Volume ownership | `docker compose exec app chown -R www-data:www-data storage` |
| Container won't start | Port conflict | Change `APP_PORT` in `.env` |
| Asset URLs show `http://` | Trusted proxies not set | Check `bootstrap/app.php` has `trustProxies(at: '*')` |

### Diagnostics

```bash
# Container status
docker compose -f docker-compose.prod.yml ps

# Container logs
docker compose -f docker-compose.prod.yml logs --tail=50 app

# Health check
curl -s https://laradate.belzacia.com/healthcheck

# Database connection
docker compose -f docker-compose.prod.yml exec app php artisan db:monitor

# Environment variables
docker compose -f docker-compose.prod.yml exec app php artisan tinker --execute="echo config('app.env');"

# Route list
docker compose -f docker-compose.prod.yml exec app php artisan route:list --except-vendor

# PHP info
docker compose -f docker-compose.prod.yml exec app php -v
docker compose -f docker-compose.prod.yml exec app php -m
```

### Performance Tuning

1. **Add Redis** — Uncomment Redis service in `docker-compose.prod.yml`, set `CACHE_STORE=redis` and `QUEUE_CONNECTION=redis`
2. **Scale queue workers** — `docker compose up -d --scale queue=3`
3. **Enable OpCache** — Already enabled in `serversideup/php` image
4. **CDN for assets** — Serve `public/build/` from Cloudflare
5. **Database indexes** — Already included in migrations
6. **Read replicas** — Add a second `db` container with replication

### Cleanup

```bash
# Remove stopped containers
docker compose -f docker-compose.prod.yml down

# Remove everything (containers, networks, volumes)
docker compose -f docker-compose.prod.yml down -v

# Remove old images
docker image prune -a --filter "until=24h"

# Check disk usage
docker system df
```

---

## CI/CD Pipeline

The `.github/workflows/deploy.yml` workflow:

```
push to main
    → checkout code
    → build Docker image (with cache)
    → run tests inside container
    → SSH to production server (62.169.22.255)
    → pull code, rebuild containers
    → run migrations
    → optimize caches
    → prune old images
```

### Required GitHub Secrets

| Secret | Description |
|--------|-------------|
| `SSH_PRIVATE_KEY` | SSH private key for server access |
| `SERVER_HOST` | `62.169.22.255` |
| `SERVER_USER` | `root` |
| `DEPLOY_PATH` | `/var/www/laradate` |

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| v1.0.0 | 2026-07-11 | Initial release — profiles, conversations, messages, Docker deployment |
