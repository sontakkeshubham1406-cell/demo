# 18. Deployment & Infrastructure Architecture

## 1. Production Infrastructure Topology

```
                                  [ Internet / Consumers ]
                                              │
                                              ▼
                         [ Cloudflare Edge Network (DNS / WAF / CDN) ]
                                              │
                                              ▼ HTTPS (Port 443)
                         [ Primary Load Balancer / Nginx Gateway ]
                                              │
                       ┌──────────────────────┴──────────────────────┐
                       ▼                                             ▼
          [ Web Node 01 (Nginx + PHP-FPM) ]             [ Web Node 02 (Nginx + PHP-FPM) ]
                       │                                             │
                       └──────────────────────┬──────────────────────┘
                                              │
                        ┌─────────────────────┼─────────────────────┐
                        ▼                     ▼                     ▼
               [ MySQL 8.0 Primary ]   [ Redis 7.x Cluster ]   [ Queue Worker Host ]
               (Write Master + Replica) (Session / Cache / Locks)(Supervisor Daemons)
                                              │
                                              ▼
                                 [ Cloudflare R2 / AWS S3 ]
                                   (Media & PDF Invoices)
```

---

## 2. Server Environment Specifications
- **Operating System**: Ubuntu 24.04 LTS (Noble Numbat) or 22.04 LTS.
- **Web Server**: Nginx 1.24+ with HTTP/2 and TLS 1.3 enabled.
- **PHP Runtime**: PHP 8.2+ or 8.3 with `php8.2-fpm`, `opcache`, `pdo_mysql`, `redis`, `gd`, `imagick`, `bcmath`, `curl`, `mbstring`.
- **Database**: MySQL 8.0 Community Server / Managed Database (AWS RDS / DigitalOcean DB).
- **In-Memory Store**: Redis 7.2 (with AOF persistence enabled for queue durability).

---

## 3. High-Concurrency Server Configurations

### 3.1 Nginx Server Block Configuration
```nginx
server {
    listen 443 ssl http2;
    server_name www.yourbrandclothing.com yourbrandclothing.com;
    root /var/www/clothing-ecommerce/public;

    index index.php index.html;

    # SSL Certificates
    ssl_certificate /etc/letsencrypt/live/yourbrandclothing.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourbrandclothing.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';" always;

    # Static Media Caching
    location ~* \.(jpg|jpeg|png|webp|gif|svg|ico|css|js|woff2)$ {
        expires 365d;
        add_header Cache-Control "public, no-transform";
        access_log off;
    }

    # Pass all dynamic requests to PHP-FPM
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 60s;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 3.2 PHP-FPM & OPcache Optimization (`php.ini`)
```ini
; OPcache Tuning for Production Performance
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0 ; Set to 0 in production; revalidate on deployment
opcache.save_comments=1

; Resource Limits
memory_limit = 256M
upload_max_filesize = 15M
post_max_size = 20M
max_execution_time = 30
```

### 3.3 Supervisor Queue Worker Daemon Configuration (`/etc/supervisor/conf.d/queue-worker.conf`)
```ini
[program:clothing-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/clothing-ecommerce/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/supervisor/clothing-worker.log
```

---

## 4. Disaster Recovery & Backup Plan
1. **Automated Database Backups**:
   - Hourly incremental binary logs.
   - Nightly encrypted full dump (`mysqldump` with `--single-transaction --quick`) compressed and uploaded to offsite S3 cold storage.
   - Retention policy: 30 daily backups, 12 monthly backups, 7 annual compliance backups.
2. **Media Assets**:
   - S3-compatible object storage with versioning enabled, protecting against accidental deletions or overwrites.
3. **Recovery Time Objective (RTO)**: `< 30 minutes`.
4. **Recovery Point Objective (RPO)**: `< 1 hour` (maximum 1 hour of data loss in catastrophic disaster).

---

## 5. Automated CI/CD Pipeline (GitHub Actions)
```yaml
name: Deploy to Production
on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, xml, ctype, iconv, intl, pdo_mysql

      - name: Install Dependencies
        run: composer install --no-dev --optimize-autoloader

      - name: Run Automated Tests
        run: ./vendor/bin/phpunit

      - name: Build Assets
        run: |
          npm ci
          npm run build

      - name: Atomic Zero-Downtime Deploy (Envoy / Deployer)
        run: |
          ssh deployer@server "cd /var/www/clothing-ecommerce && git pull origin main && php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && sudo supervisorctl restart all"
```

