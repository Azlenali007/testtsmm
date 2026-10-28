# ApexSMM — Social Media Marketing Panel

ApexSMM is an enterprise-grade, high-performance Social Media Marketing (SMM) Panel engineered from scratch using **Core PHP 8.2+**, **MySQL / MariaDB (InnoDB)**, **Tailwind CSS**, and **Alpine.js**.

It provides an end-to-end platform for social media growth resellers, marketing agencies, and direct clients. It connects to wholesale upstream SMM providers using the standard **SMM v2 JSON API protocol**, dispatches orders instantly, and features atomic ledger accounting.

---

## Key Highlights

* **Pure Core PHP (Zero Frameworks)**: Lightweight, modular, fast. No bloated framework dependencies (no Laravel, Symfony, or CodeIgniter).
* **Strict Zero-TS Compliance**: Zero `.ts`, `.tsx`, or `.jsx` files anywhere in the application. Clean standard `.php`, `.js`, and `.css` files.
* **Atomic Ledger Accounting**: Every balance debit, credit, and refund uses `SELECT ... FOR UPDATE` row locks inside database transactions. Immutably logged to `wallet_transactions`.
* **Standard SMM v2 API**: Full JSON API implementation supporting actions: `services`, `add`, `status`, `refill`, `refill_status`, and `balance`.
* **Multi-Theme Engine**: Extensible `ThemeManager` architecture that allows swapping frontend designs (`resources/views/themes/{theme_name}`) without touching backend business logic or database queries.
* **Automated Cron Daemons**: Background processing with `flock` concurrency locking for order tracking, catalog auto-sync, drip-feed pacing, refill dispatches, and daily accounting reports.
* **Modern UI & Interactions**: Clean Tailwind CSS layout with Alpine.js reactivity, live order price calculation, instant filters, search modals, and Chart.js telemetry.

---

## Technology Stack

| Layer | Technology | Rationale |
| :--- | :--- | :--- |
| **Backend** | Core PHP 8.2+ (Strict types) | Native speed, zero framework bloat, maximum portability |
| **Database** | MySQL / MariaDB 10.6+ | InnoDB engine, strict foreign key constraints, `DECIMAL(15,4)` financial precision |
| **Database Access** | PDO with Prepared Statements | Complete SQL injection protection, prepared parameters |
| **CSS Styling** | Tailwind CSS | Utility-first responsive design, dark mode, consistent design system |
| **Interactivity** | Alpine.js (Standalone) | Lightweight declarative DOM state without a heavy SPA build system |
| **HTTP Requests** | Vanilla JS Fetch API | Modern browser asynchronous calls with CSRF header injection |
| **Icons** | Inline SVG | Scalable, lightweight, zero external web-font dependencies |
| **Analytics** | Chart.js | Visual 30-day revenue and order volume trends |
| **Background Jobs**| PHP CLI Scripts | Cron-compatible daemons with file-locking concurrency control |

---

## Architectural Components (`app/Core/`)

1. **`Router.php`**: Fast, lightweight regex router supporting route groups, URI parameter extraction (`/orders/{id}`), prefixes (`/admin`), and middleware pipelines (`auth`, `admin`, `guest`, `csrf`).
2. **`Database.php`**: Singleton PDO connection manager with socket fallback and a nested-transaction-safe `Database::transaction(callable)` runner.
3. **`ThemeManager.php`**: Dynamically discovers installed themes in `resources/views/themes/`, reads the active theme from the database, and resolves view, layout, and component paths with automatic fallback to `classic`.
4. **`View.php`**: Rendering pipeline that automatically shares session state, user authentication, and cryptographic CSRF tokens with views.
5. **`Auth.php`**: Secure session-based authentication with `password_hash()` (bcrypt cost 12), role verification, and session regeneration.
6. **`Csrf.php`**: Cryptographic CSRF tokens stored in session, rendered in forms via `Csrf::field()`, and validated via timing-attack-safe `hash_equals()`.
7. **`Validator.php`**: Server-side request validation (`required`, `email`, `min`, `max`, `numeric`, `unique`, `url`).
8. **`ErrorHandler.php`**: Centralized error and exception logger writing structured stack traces to `storage/logs/app_YYYY-MM-DD.log`.

---

## Standard SMM v2 API Specification

ApexSMM provides full compatibility with the universal SMM v2 protocol at endpoint:
```text
POST /api/v2
Content-Type: application/x-www-form-urlencoded
```

### 1. Service List
* **Parameters**: `key=YOUR_API_KEY&action=services`
* **Response**:
```json
[
  {
    "service": 1,
    "name": "Instagram Followers [High Quality - Non-Drop]",
    "type": "Default",
    "category": "Instagram Followers & Likes",
    "rate": "1.2500",
    "min": 50,
    "max": 50000,
    "refill": true,
    "cancel": false
  }
]
```

### 2. Add Order
* **Parameters**: `key=YOUR_API_KEY&action=add&service=1&link=https://instagram.com/user&quantity=1000`
* **Response**:
```json
{
  "order": 10582
}
```

### 3. Check Order Status
* **Parameters**: `key=YOUR_API_KEY&action=status&order=10582`
* **Multi-order check**: `key=YOUR_API_KEY&action=status&orders=10582,10583,10584`
* **Response**:
```json
{
  "charge": "1.2500",
  "start_count": 1420,
  "status": "In progress",
  "remains": 350,
  "currency": "USD"
}
```

### 4. Check Balance
* **Parameters**: `key=YOUR_API_KEY&action=balance`
* **Response**:
```json
{
  "balance": "99.88",
  "currency": "USD"
}
```

---

## How to Add a New Theme

The theme engine is completely decoupled from business logic:
1. Create a folder in `resources/views/themes/your_theme_name/`.
2. Add `layouts/main.php`, `layouts/admin.php`, and `layouts/auth.php`.
3. Add page templates corresponding to views in `resources/views/themes/your_theme_name/pages/`.
4. Any missing view in your new theme automatically falls back to `resources/views/themes/classic/pages/`.
5. In the Super Admin panel (`/admin/settings`), select your new theme and click **Save Settings**.

---

## Default Credentials

* **Super Admin**: `admin` / `Password123!` (Access at `/admin` or `/login`)
* **Demo Client**: `demo_user` / `Password123!` (Access at `/dashboard` or `/login`)
* **Demo Client API Key**: `usr_1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d`
