# ApexSMM Panel — Complete Installation & Deployment Guide

This document describes how to deploy, configure, and maintain the ApexSMM Social Media Marketing Panel built on **Core PHP 8.2+**, **MySQL / MariaDB (InnoDB)**, **Tailwind CSS**, and **Alpine.js**.

---

## 1. System Requirements

* **Operating System**: Linux (Debian 12, Ubuntu 22.04/24.04, RHEL/AlmaLinux 9) or any UNIX-like environment.
* **PHP**: Version 8.2 or higher (tested on PHP 8.2.26).
  * Required PHP Extensions: `pdo_mysql`, `curl`, `mbstring`, `xml`, `json`, `session`, `fileinfo`.
* **Database**: MySQL 8.0+ or MariaDB 10.6+ with InnoDB storage engine.
* **Web Server**: Apache2 (with `mod_rewrite`), Nginx, or PHP CLI built-in webserver.

---

## 2. Directory Structure

```text
/
├── app/
│   ├── Controllers/         # Application request controllers (Web & Admin)
│   ├── Core/                # Framework-free kernel (Router, Database, Session, View, etc.)
│   ├── Helpers/             # Escaping (Security), Formatting (Format)
│   ├── Middleware/          # Auth, Admin, Guest, Csrf middleware
│   ├── Services/            # Business logic (OrderService, WalletService, ProviderService)
│   └── bootstrap.php        # Global initialization & autoloader
├── config/
│   ├── app.php              # Application URL and timezone config
│   └── database.php         # PDO credentials & options
├── cron/                    # Background task automation (check_orders, sync_services, etc.)
├── database/
│   ├── schema.sql           # Complete 22-table schema with foreign keys
│   └── seed.sql             # Default super admin, demo user, categories, services & gateways
├── modules/
│   ├── Payments/            # Payment gateway contracts
│   └── Providers/           # Standard SMM v2 API provider client
├── public/                  # Document root
│   ├── assets/              # Compiled CSS (Tailwind), standalone JS (Alpine.js, Chart.js)
│   └── index.php            # Unified Front Controller
├── resources/
│   └── views/
│       └── themes/
│           └── classic/     # Default responsive dark-mode theme
│               ├── layouts/ # main.php, admin.php, auth.php
│               └── pages/   # 27 view templates across user, admin, auth, and errors
├── storage/                 # Logs, file lock descriptors, temporary cache
├── .env.example             # Environment template
├── FEATURE_INVENTORY.md     # Complete feature audit and implementation matrix
├── INSTALL.md               # This deployment guide
└── README.md                # Architectural documentation & API spec
```

---

## 3. Database Setup

1. Create a database in MySQL / MariaDB:
   ```sql
   CREATE DATABASE smm_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'smm_user'@'localhost' IDENTIFIED BY 'smm_pass123!';
   GRANT ALL PRIVILEGES ON smm_panel.* TO 'smm_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

2. Import the schema and initial seed data:
   ```bash
   mysql -u smm_user -psmm_pass123! smm_panel < database/schema.sql
   mysql -u smm_user -psmm_pass123! smm_panel < database/seed.sql
   ```

---

## 4. Default Seeded Credentials

### Super Administrator
* **URL**: `/login` (redirects to `/admin`)
* **Username**: `admin`
* **Password**: `Password123!`
* **Role**: `super_admin`

### Demo Client User
* **URL**: `/login` (redirects to `/dashboard`)
* **Username**: `demo_user`
* **Password**: `Password123!`
* **Balance**: `$100.00`
* **API Key**: `usr_1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d`

---

## 5. Web Server Configuration

### Document Root
Ensure the web server points directly to the `/public` folder:

### Nginx Virtual Host
```nginx
server {
    listen 80;
    server_name smm.yourdomain.com;
    root /path/to/smm_panel/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache `.htaccess` (placed in `/public/.htaccess`)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>
```

### Development Built-in Server
To run instantly on port 3000:
```bash
php -S 0.0.0.0:3000 -t public public/index.php
```

---

## 6. Background Cron Jobs (Crontab)

All background jobs use non-blocking `flock()` to prevent overlapping execution. Add the following to your system `crontab -e`:

```cron
# Synchronize provider order statuses every 2 minutes
*/2 * * * * php /path/to/smm_panel/cron/check_orders.php >> /path/to/smm_panel/storage/logs/cron_orders.log 2>&1

# Sync provider catalogs & rates every 4 hours
0 */4 * * * php /path/to/smm_panel/cron/sync_services.php >> /path/to/smm_panel/storage/logs/cron_sync.log 2>&1

# Dispatch drip-feed intervals every minute
* * * * * php /path/to/smm_panel/cron/dripfeed.php >> /path/to/smm_panel/storage/logs/cron_dripfeed.log 2>&1

# Process automated refill requests every 15 minutes
*/15 * * * * php /path/to/smm_panel/cron/refill.php >> /path/to/smm_panel/storage/logs/cron_refill.log 2>&1

# Aggregate financial & order reports daily at midnight
0 0 * * * php /path/to/smm_panel/cron/daily_reports.php >> /path/to/smm_panel/storage/logs/cron_reports.log 2>&1
```

---

## 7. Security Best Practices Enforced

1. **Native Prepared Statements**: All dynamic input is bound via parameterized PDO queries (`:param`). Zero SQL string concatenations.
2. **Cryptographic CSRF Tokens**: Timing-attack-safe token verification on all POST/PUT/DELETE requests via `hash_equals()`.
3. **Atomic Wallet Accounting**: All financial balance mutations execute inside `Database::transaction()` using `SELECT ... FOR UPDATE` row locks and immutable audit entries in `wallet_transactions`.
4. **Session Hardening**: Cookies configured with `HttpOnly`, `SameSite=Lax`, and `Secure` flags. Session identifiers regenerate upon login.
5. **Role-Based Access Control**: Middleware verifies admin capabilities before any `/admin/*` route execution.
