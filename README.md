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

For LAN access from this machine, use:

```env
APP_URL=http://192.168.31.6
VITE_DEV_SERVER_HOST=192.168.31.6
VITE_DEV_SERVER_PORT=5173
```

Create the database in phpMyAdmin (or MySQL CLI), then run migrations + seeders:

```bash
php artisan migrate --seed
```

If you changed profile photo handling and storage links are needed:

```bash
php artisan storage:link
```

---

## 4) Run the app

Run frontend dev server:

```bash
npm run dev
```

Run backend server:

```bash
php artisan serve --host=192.168.31.6 --port=80
```

Open:

- Laravel API/UI shell: `http://192.168.31.6`

If port 80 is already used by Apache/IIS, run Laravel on port 8000 instead:

```bash
php artisan serve --host=192.168.31.6 --port=8000
```

Then set `APP_URL=http://192.168.31.6:8000` and open `http://192.168.31.6:8000`.

---

## 5) Activity Logs (Audit Trail)

The system now includes an **Activity Logs** page for monitoring operations:

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

## 6) Notification behavior

Notifications are created for relevant recipients, but the system intentionally avoids notifying the same user who triggered the action.

This keeps alerts meaningful and reduces noise for operators.

---

## 7) Access control model

Authenticated users can access read operations.

Write operations (create/update/delete and stock operations) are restricted to:

- `admin`
- `manager`
- `asset_officer`

Settings write endpoints are restricted to:

- `admin`

---

## 8) Recommended developer commands

```bash
# PHP syntax checks
php -l app/Http/Controllers/ItemController.php

# Clear cached config/routes/views after env changes
php artisan optimize:clear

# Run tests (when dependencies are fully installed)
php artisan test
```

---

## 9) Troubleshooting

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

## 10) Production notes

- Use `APP_ENV=production` and `APP_DEBUG=false`
- Configure queue/mail drivers properly
- Use HTTPS
- Use strong credentials and rotate secrets
