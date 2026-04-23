# NextGen Assets Management System

NextGen Assets Management System is a Laravel + React application for managing inventory, assignments, users, suppliers, and operational visibility.

## Core modules

- Dashboard
- Inventory / Items
- Assignments
- Categories / Departments / Suppliers
- Users and profile management
- Notifications
- **Activity Logs (Audit Trail)**
- Settings / Branding

---

## 1) Prerequisites (Windows + XAMPP friendly)

Install:

- PHP `8.2+`
- Composer `2+`
- Node.js `18+` (Node `20+` recommended)
- npm
- MySQL / MariaDB (XAMPP is fine)
- Git

> If you are using XAMPP, make sure Apache and MySQL are running before starting Laravel.

---

## 2) Clone and install

```bash
git clone https://github.com/austinkalisik/Nextgen_Assets_Management_System.git
cd Nextgen_Assets_Management_System
composer install
npm install
```

---

## 3) Environment setup

Copy environment file and generate app key:

```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` database values (example for local XAMPP):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nextgen_assets
DB_USERNAME=root
DB_PASSWORD=
```

Create the database in phpMyAdmin (or MySQL CLI), then run migrations + seeders:

```bash
php artisan migrate --seed
php artisan storage:link
```

Clear cached configuration after environment changes:

```bash
php artisan optimize:clear
```

---

## 4) Run locally for development

Use two terminals:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

```bash
npm run dev
```

Open:

- `http://127.0.0.1:8000`

VS Code users can run:

```text
Ctrl + Shift + P -> Tasks: Run Task -> NextGen: Start App Local
```

---

## 5) Run on an office LAN

Find the server PC LAN IP, then update `.env`. Example:

```env
APP_URL=http://192.168.31.34:8000
VITE_DEV_SERVER_BIND_HOST=0.0.0.0
VITE_DEV_SERVER_HOST=192.168.31.34
VITE_DEV_SERVER_PORT=5173
```

Run:

```bash
npm run serve:lan
npm run dev:lan
```

Open:

- `http://192.168.31.34:8000`

VS Code users can run:

```text
Ctrl + Shift + P -> Tasks: Run Task -> NextGen: Start App LAN
```

Allow Windows Firewall inbound TCP ports `8000` and `5173` if other office PCs cannot connect.

---

## 6) Test and verify before pushing

Recommended verification:

```bash
php artisan test
npm run build
```

Useful targeted checks:

```bash
php artisan test --filter=Notification
php artisan test --filter=AssignmentQuantityValidationTest
```

---

## 7) Activity Logs (Audit Trail)

The system includes an **Activity Logs** page for monitoring operations:

- asset creation/update/deletion
- assignment and return actions
- stock in/out actions

API endpoint:

- `GET /api/activity-logs`

Main filters supported:

- `search`
- `action`
- `user_id`
- `item_id`
- `date_from`
- `date_to`
- `per_page`

---

## 8) Notification behavior

Notifications are created for relevant recipients, but the system intentionally avoids notifying the same user who triggered the action.

This keeps alerts meaningful and reduces noise for operators.

---

## 9) Access control model

Authenticated users can access read operations.

Write operations (create/update/delete and stock operations) are restricted to:

- `admin`
- `manager`
- `asset_officer`

Settings write endpoints are restricted to:

- `admin`

---

## 10) Recommended developer commands

```bash
# PHP syntax checks
php -l app/Http/Controllers/ItemController.php

# Clear cached config/routes/views after env changes
php artisan optimize:clear

# Run tests (when dependencies are fully installed)
php artisan test
```

---

## 11) Troubleshooting

### Composer install fails

- Confirm internet/proxy access.
- Retry with:

```bash
composer clear-cache
composer install
```

### Database connection errors

- Verify `.env` DB credentials.
- Ensure MySQL service is running.
- Confirm DB exists.

### Frontend not loading assets

- Make sure `npm run dev` is running.
- Clear browser cache/hard refresh.

### Route/cache weird behavior after pulling changes

```bash
php artisan optimize:clear
php artisan migrate
```

---

## 12) Production notes

- Use `APP_ENV=production` and `APP_DEBUG=false`
- Configure queue/mail drivers properly
- Use HTTPS
- Use strong credentials and rotate secrets
