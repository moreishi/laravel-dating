# LaraDate — Production Deployment Guide

## Overview

LaraDate is a Laravel 13 dating application with profile browsing, conversations, and messaging. This guide covers end-to-end production deployment.

### Key Features

- User registration & authentication (Breeze Blade)
- Profile management (age, bio, gender)
- Profile browsing with pagination
- One-on-one conversations
- Real-time messaging via HTMX
- Rate-limited API endpoints
- Authorization policies for conversations & messages

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Framework** | Laravel 13.x |
| **PHP** | 8.4 |
| **Database** | MariaDB 10.11 / MySQL 8.0+ / PostgreSQL 16+ |
| **Web Server** | nginx |
| **Frontend** | Blade + Tailwind CSS 3.x + Alpine.js 3.x + HTMX 2.x |
| **Asset Bundler** | Vite 8.x |
| **Data Layer** | Spatie laravel-data 4.x (DTOs) |
| **Queue** | Database driver (default) |
| **Cache** | Database driver (default) |
| **Session** | Database driver (default) |
| **Local Dev** | DDEV (nginx-fpm, MariaDB 10.11) |

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

- **Repository Pattern** — Interfaces (`App\Interfaces`) bound to implementations (`App\Repositories`) via `RepositoryServiceProvider`
- **Action Classes** — Single-responsibility operations (`StartConversationAction`, `SendMessageAction`)
- **DTOs** — Spatie `laravel-data` for typed input validation
- **Policies** — `ConversationPolicy`, `MessagePolicy` for authorization
- **Observers** — `MessageObserver` auto-touches conversation `updated_at`
- **No API Resources** — Views render directly from controllers

### Data Flow

```
User Action → Route → Controller → Authorize (Policy) → Action → Service → Repository → Model → DB
                                                                                            ↓
User ← View ← Controller ← Policy check ← Model ← Repository ← Service ← Action ←──────────┘
```

---

## Local Development (DDEV)

> See `README.md` for detailed local setup. Quick reference:

```bash
ddev start
ddev composer install
ddev php artisan migrate --seed
ddev npm install && ddev npm run build
```

Default test account: `test@example.com` / `password`

---

## Production Deployment

### Server Requirements

- PHP 8.3+ with extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenxml`, `xml`, `xsl`, `zip`
- Composer 2.x
- Node.js 20+ & npm (for initial build only)
- Database: MariaDB 10.11+ / MySQL 8.0+
- Web server: nginx
- Supervisor (for queue worker)

### Option 1: Laravel Forge (Recommended)

1. Create server on Forge (Ubuntu 24.04, PHP 8.4, MariaDB/MySQL)
2. Add site → point to repo `git@github.com:moreishi/laravel-dating.git`
3. Set deployment script:

```bash
cd /home/forge/your-domain.com
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
npm install --ignore-scripts
npm run build
```

4. Set daemon (queue worker) → `php artisan queue:work --sleep=3 --tries=3`
5. Add scheduled job → `php artisan schedule:run` (every minute)
6. Configure SSL via Let's Encrypt

### Option 2: Manual Ubuntu Server

<details>
<summary>Click to expand</summary>

#### 1. Provision Server

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.4
sudo add-apt-repository ppa:ondrej/php -y
sudo apt install php8.4-fpm php8.4-cli php8.4-mysql php8.4-bcmath \
                 php8.4-ctype php8.4-curl php8.4-dom php8.4-fileinfo \
                 php8.4-gd php8.4-intl php8.4-mbstring php8.4-xml \
                 php8.4-zip php8.4-tokenizer -y

# Install nginx
sudo apt install nginx -y

# Install MariaDB
sudo apt install mariadb-server -y
sudo mysql_secure_installation

# Install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install nodejs -y
```

#### 2. Configure Database

```sql
CREATE DATABASE laradate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'laradate'@'localhost' IDENTIFIED BY 'strong-password-here';
GRANT ALL PRIVILEGES ON laradate.* TO 'laradate'@'localhost';
FLUSH PRIVILEGES;
```

#### 3. Deploy Application

```bash
# Create app directory
sudo mkdir -p /var/www/laradate
sudo chown -R $USER:$USER /var/www/laradate

# Clone repo
git clone git@github.com:moreishi/laravel-dating.git /var/www/laradate
cd /var/www/laradate

# Install dependencies
composer install --no-dev --optimize-autoloader

# Environment
cp .env.example .env
php artisan key:generate

# Build assets
npm install --ignore-scripts
npm run build

# Migrate
php artisan migrate --force

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

#### 4. nginx Configuration

Create `/etc/nginx/sites-available/laradate`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/laradate/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ /\.(?!well-known) {
        deny all;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|webp|woff|woff2|ttf|eot)$ {
        expires 365d;
        add_header Cache-Control "public, immutable";
    }

    # Deny .env file access
    location ~ /\.env {
        deny all;
    }

    # Build assets
    location /build/ {
        alias /var/www/laradate/public/build/;
        expires 365d;
        add_header Cache-Control "public, immutable";
    }

    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml text/javascript image/svg+xml;
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/laradate /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 5. Queue Worker (Supervisor)

Install and configure Supervisor:

```bash
sudo apt install supervisor -y
```

Create `/etc/supervisor/conf.d/laradate-worker.conf`:

```ini
[program:laradate-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laradate/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/laradate/storage/logs/queue-worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laradate-worker:*
```

#### 6. Crontab (Scheduler)

```bash
# Edit crontab for www-data: sudo crontab -u www-data -e
* * * * * cd /var/www/laradate && php artisan schedule:run >> /dev/null 2>&1
```

#### 7. File Permissions

```bash
sudo chown -R www-data:www-data /var/www/laradate/storage
sudo chown -R www-data:www-data /var/www/laradate/bootstrap/cache
sudo chmod -R 775 /var/www/laradate/storage
sudo chmod -R 775 /var/www/laradate/bootstrap/cache
```

</details>

### Option 3: Docker Production

<details>
<summary>Click to expand</summary>

#### 1. Production Docker Compose

Create `docker-compose.prod.yml`:

```yaml
services:
  app:
    image: serversideup/php:8.4-fpm-nginx
    container_name: laradate-app
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - .:/var/www/html
      - storage:/var/www/html/storage
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_HOST=db
      - DB_DATABASE=laradate
      - DB_USERNAME=laradate
      - DB_PASSWORD=${DB_PASSWORD}
    depends_on:
      db:
        condition: service_healthy
    ports:
      - "8080:80"

  queue:
    image: serversideup/php:8.4-fpm
    container_name: laradate-queue
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - .:/var/www/html
      - storage:/var/www/html/storage
    entrypoint: ["php", "artisan", "queue:work", "--sleep=3", "--tries=3"]
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_HOST=db
      - DB_DATABASE=laradate
      - DB_USERNAME=laradate
      - DB_PASSWORD=${DB_PASSWORD}
    depends_on:
      db:
        condition: service_healthy

  scheduler:
    image: serversideup/php:8.4-fpm
    container_name: laradate-scheduler
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - .:/var/www/html
      - storage:/var/www/html/storage
    entrypoint: ["php", "artisan", "schedule:work"]
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_HOST=db
      - DB_DATABASE=laradate
      - DB_USERNAME=laradate
      - DB_PASSWORD=${DB_PASSWORD}
    depends_on:
      db:
        condition: service_healthy

  db:
    image: mariadb:10.11
    container_name: laradate-db
    restart: unless-stopped
    environment:
      - MARIADB_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
      - MARIADB_DATABASE=laradate
      - MARIADB_USER=laradate
      - MARIADB_PASSWORD=${DB_PASSWORD}
    volumes:
      - dbdata:/var/lib/mysql
    healthcheck:
      test: ["CMD", "healthcheck.sh", "--connect"]
      interval: 10s
      timeout: 5s
      retries: 5

volumes:
  storage:
  dbdata:
```

#### 2. Deploy with Docker

```bash
# Set up environment variables in .env
# Build and start
docker compose -f docker-compose.prod.yml up -d --build

# Run migrations
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Cache
docker compose -f docker-compose.prod.yml exec app php artisan optimize
```

</details>

---

## Environment Configuration

### `.env` Production Template

```bash
APP_NAME=LaraDate
APP_ENV=production
APP_KEY=base64:xxxxxxxxxxxxxxxx   # GENERATE WITH: php artisan key:generate
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database (MariaDB / MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laradate
DB_USERNAME=laradate
DB_PASSWORD=your-strong-db-password

# Session (use database in production)
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_SECURE_COOKIE=true     # ⚠️ HTTPS only

# Cache (use database or redis for production)
CACHE_STORE=database
# CACHE_STORE=redis
# REDIS_HOST=127.0.0.1
# REDIS_PASSWORD=null
# REDIS_PORT=6379

# Queue (use database in production)
QUEUE_CONNECTION=database

# Mail (configure your provider)
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Logging
LOG_LEVEL=warning

# Filesystem (use S3 for production)
FILESYSTEM_DISK=local
# AWS_ACCESS_KEY_ID=
# AWS_SECRET_ACCESS_KEY=
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=your-bucket
```

### Variable Reference

| Variable | Production Value | Notes |
|----------|-----------------|-------|
| `APP_ENV` | `production` | Must be `production` |
| `APP_DEBUG` | `false` | Never `true` in production |
| `APP_KEY` | Generated key | `php artisan key:generate` |
| `SESSION_SECURE_COOKIE` | `true` | Requires HTTPS |
| `LOG_LEVEL` | `warning` | Prevents log flooding |
| `DB_CONNECTION` | `mysql` | Or `pgsql` for PostgreSQL |
| `QUEUE_CONNECTION` | `database` | Or `redis` for higher throughput |
| `CACHE_STORE` | `database` | Or `redis` for better performance |

---

## Security Checklist

Run through this checklist before going live:

- [ ] **`APP_ENV=production`** — never `local` or `dev`
- [ ] **`APP_DEBUG=false`** — prevents stack trace leaks
- [ ] **`APP_KEY` generated** — run `php artisan key:generate`
- [ ] **`SESSION_SECURE_COOKIE=true`** — HTTPS only cookies
- [ ] **`SESSION_DRIVER=database`** — file sessions are not suitable for production
- [ ] **Rate limiting active** — `throttle:5,1` on conversation creation, `throttle:10,1` on messages
- [ ] **CSRF protection** — verify all POST forms include `@csrf`
- [ ] **HTTPS enforced** — nginx redirects HTTP → HTTPS
- [ ] **.env file protected** — blocked via nginx location rule
- [ ] **Storage directory locked** — `chmod 775`, owned by `www-data`
- [ ] **Debug mode disabled** — double-check no debug packages in production
- [ ] **Headers configured** — `X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`
- [ ] **Queue worker running** — supervised to auto-restart
- [ ] **Database backup configured** — automated daily backups
- [ ] **SSL/TLS active** — Let's Encrypt or commercial cert
- [ ] **Fail2ban installed** — protect against brute force login attempts
- [ ] **Email verification** — `verified` middleware on `/dashboard` route

### Deployment Command

One-liner after code push:

```bash
git pull origin main && \
composer install --no-dev --optimize-autoloader && \
php artisan migrate --force && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
php artisan event:cache && \
npm install --ignore-scripts && \
npm run build && \
sudo supervisorctl restart laradate-worker:*
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
php artisan migrate --force

# Rollback last batch
php artisan migrate:rollback --step=1 --force

# Fresh migrate + seed (local only)
php artisan migrate:fresh --seed

# Reset and re-run all migrations
php artisan migrate:fresh --force

# View migration status
php artisan migrate:status

# Seed with pre-configured accounts
php artisan db:seed --force

# View database stats
php artisan db:show
```

### Backup Strategy

```bash
#!/bin/bash
# /etc/cron.daily/laradate-db-backup

BACKUP_DIR=/var/backups/laradate
mkdir -p "$BACKUP_DIR"
DATE=$(date +%Y%m%d-%H%M%S)

# Database dump
mysqldump --single-transaction --routines --events \
  -u laradate -p'your-password' laradate \
  | gzip > "$BACKUP_DIR/laradate-db-$DATE.sql.gz"

# Encrypt (optional)
# gpg --encrypt --recipient your-key "$BACKUP_DIR/laradate-db-$DATE.sql.gz"

# Keep 30 days
find "$BACKUP_DIR" -name "*.sql.gz" -mtime +30 -delete

# Sync to remote storage (optional)
# aws s3 sync "$BACKUP_DIR" s3://your-bucket/backups/
```

---

## Monitoring & Maintenance

### Health Check

Laravel's built-in health check is available at `/up` (configured in `bootstrap/app.php`). Use with your monitoring tool:

```bash
curl -s -o /dev/null -w "%{http_code}" https://your-domain.com/up
# Expected: 200
```

### Logs

```bash
# Application logs
tail -f /var/www/laradate/storage/logs/laravel.log

# Queue worker logs
tail -f /var/www/laradate/storage/logs/queue-worker.log

# Nginx access
tail -f /var/log/nginx/laradate.access.log

# Nginx errors
tail -f /var/log/nginx/laradate.error.log

# PHP-FPM
journalctl -u php8.4-fpm --since "1 hour ago"
```

### Load Testing

Use tools like [k6](https://k6.io/) or [Apache Bench](https://httpd.apache.org/docs/2.4/programs/ab.html) before scaling:

```bash
# Simple load test with ab
ab -n 1000 -c 10 https://your-domain.com/profiles

# With k6
k6 run --vus 10 --duration 30s -e BASE_URL=https://your-domain.com loadtest.js
```

### Caching Commands

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild all caches (run after each deploy)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Maintenance Mode

```bash
# Enable maintenance mode
php artisan down --retry=60 --render="errors::503"

# Allow specific IPs
php artisan down --allow=203.0.113.0/24

# Disable maintenance mode
php artisan up
```

---

## Troubleshooting

### Common Issues

| Symptom | Cause | Fix |
|---------|-------|-----|
| `APP_KEY` error | Missing or invalid app key | `php artisan key:generate` |
| 419 page expired | CSRF token mismatch | Clear cookies, regenerate app key, verify `@csrf` in forms |
| 500 error after deploy | Cache outdated | `php artisan optimize:clear` |
| Login redirect loop | `SESSION_SECURE_COOKIE` mismatch | Set `SESSION_SECURE_COOKIE=true` with HTTPS, `false` for HTTP |
| Class not found | Autoloader cache stale | `composer dump-autoload` |
| Route not found | Route cache stale | `php artisan route:clear` |
| Too many redirects | HTTP→HTTPS loop | Check nginx SSL config, verify `APP_URL` uses HTTPS |
| Blank page | PHP error, debug disabled | Check `storage/logs/laravel.log` |
| Messages not sending | Queue worker not running | `sudo supervisorctl status laradate-worker:*` |
| Upload failures | Storage permissions | `chown -R www-data:www-data storage/` |
| Vite manifest missing | Assets not built | `npm install && npm run build` |

### Diagnostics

```bash
# Quick health check
php artisan about --json

# Check PHP version and extensions
php -v
php -m | grep -E "pdo|mysql|bcmath|curl|gd"

# Test database connection
php artisan db:monitor

# Verify .env is loaded
php artisan tinker --execute="echo config('app.env');"

# Check all routes
php artisan route:list --except-vendor

# Verify queue is processing
php artisan queue:monitor
```

### Performance Tuning

If the app becomes slow under load:

1. **Switch cache to Redis** — Set `CACHE_STORE=redis` and configure Redis
2. **Switch queue to Redis** — Set `QUEUE_CONNECTION=redis` for faster job processing
3. **Add more queue workers** — Increase `numprocs` in supervisor config
4. **Enable OpCache** — Precompile PHP bytecode:

```ini
; /etc/php/8.4/fpm/conf.d/10-opcache.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

5. **Database indexes** — The migrations already include indexes on `conversation_user(user_id)` and `messages(conversation_id)`
6. **CDN for assets** — Serve built assets from Cloudflare or similar
7. **Database read replicas** — Separate read/write connections for high traffic

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| v1.0.0 | 2026-07-11 | Initial release — scaffold, profiles, conversations, messages |
