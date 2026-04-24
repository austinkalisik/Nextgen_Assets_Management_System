# Nextgen Assets Management System

Nextgen Assets Management System is a Laravel + ReactJS CRUD application for managing inventory, assignments, stock movement, notifications, activity logs, branding, and office operations.

It is owned by **Nextgen Technology** and can be used internally by Nextgen Technology or rolled out to other departments that want to adopt the software.

Repository:
- `https://github.com/austinkalisik/Nextgen_Assets_Management_System`

## What you get

- Dashboard and system overview
- Inventory / Items with depreciation support
- Assignments and returns
- Suppliers, categories, and departments
- Users and profile management
- Notifications
- Activity logs / audit trail
- Settings / branding

## Stack

- Backend: Laravel 13
- Frontend: React 19 + Vite
- Database: MySQL / MariaDB
- PDF support: `barryvdh/laravel-dompdf`

## Requirements

- PHP `8.3+`
- Composer `2+`
- Node.js `20+`
- npm
- MySQL / MariaDB
- Git

For Windows/XAMPP users:
- start MySQL before running migrations
- Apache is optional because local development can use `php artisan serve`

## Fastest local setup

After cloning, the easiest setup path is:

```bash
git clone https://github.com/austinkalisik/Nextgen_Assets_Management_System.git
cd Nextgen_Assets_Management_System
composer run bootstrap
```

What `composer run bootstrap` does:
- installs Composer dependencies
- creates `.env` from `.env.example` if missing
- generates `APP_KEY` if missing
- creates the storage link
- runs migrations and seeders
- clears cached Laravel state
- installs npm dependencies
- builds frontend assets

Important:
- update your `.env` database credentials before running bootstrap if you are not using the default local MySQL setup

## Manual local setup

If you prefer doing it step by step:

```bash
git clone https://github.com/austinkalisik/Nextgen_Assets_Management_System.git
cd Nextgen_Assets_Management_System
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
php artisan optimize:clear
npm run build
```

Example `.env` database values for local MySQL/XAMPP:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nextgen_assets
DB_USERNAME=root
DB_PASSWORD=
```

## Running in development

Use two terminals:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

```bash
npm run dev
```

Open:
- `http://127.0.0.1:8000`

## Running in VS Code

This repo includes ready-made VS Code tasks in [.vscode/tasks.json](</\\?\UNC\192.168.31.6\Austin\Testing\Testing AMS\Nextgen_Assets_Management_System\.vscode\tasks.json:1>).

Useful tasks:
- `NextGen: Bootstrap Project`
- `NextGen: Start App Local`
- `NextGen: Start App LAN`
- `NextGen: Verify Project`
- `NextGen: Clear Laravel Cache`

Run them from:

```text
Ctrl + Shift + P -> Tasks: Run Task
```

## Seeded accounts

Default seeded users:

- `admin@nextgen.local` / `password`
- `assets@nextgen.local` / `password`
- `support@nextgen.local` / `password`
- `operations@nextgen.local` / `password`

## Office LAN development

Update `.env`:

```env
APP_URL=http://192.168.31.34:8000
VITE_DEV_SERVER_BIND_HOST=0.0.0.0
VITE_DEV_SERVER_HOST=192.168.31.34
VITE_DEV_SERVER_PORT=5173
```

Then run:

```bash
npm run serve:lan
npm run dev:lan
```

Allow Windows Firewall inbound TCP ports `8000` and `5173` if needed.

## Docker quick start

This repo now includes:
- [Dockerfile](</\\?\UNC\192.168.31.6\Austin\Testing\Testing AMS\Nextgen_Assets_Management_System\Dockerfile:1>)
- [docker-compose.yml](</\\?\UNC\192.168.31.6\Austin\Testing\Testing AMS\Nextgen_Assets_Management_System\docker-compose.yml:1>)
- [.env.docker.example](</\\?\UNC\192.168.31.6\Austin\Testing\Testing AMS\Nextgen_Assets_Management_System\.env.docker.example:1>)

Run:

```bash
docker compose up --build
```

Open:
- `http://127.0.0.1:8000`

Docker notes:
- the app container runs migrations and seeders on startup
- MySQL is exposed on host port `3307`
- Docker uses `.env.docker.example` inside the container unless you provide your own `.env`

## Verification

Run the standard verification checks:

```bash
composer verify
```

That runs:
- Laravel tests
- frontend production build

Current status:
- `php artisan test` passes
- `npm run build` passes

## Production readiness

This repo is now easier to clone and run, but production deployment still needs normal environment hardening.

Before production:
- set `APP_ENV=production`
- set `APP_DEBUG=false`
- use real database credentials
- configure real mail settings
- use HTTPS
- use a proper process manager / web server setup
- configure backups and monitoring
- review queue and storage strategy

## Documentation references

- [wireframe/ERD - Nextgen Assets Management System.txt](</\\?\UNC\192.168.31.6\Austin\Testing\Testing AMS\Nextgen_Assets_Management_System\wireframe\ERD%20-%20Nextgen%20Assets%20Management%20System.txt:1>)
- [wireframe/Schema Alignment Notes.txt](</\\?\UNC\192.168.31.6\Austin\Testing\Testing AMS\Nextgen_Assets_Management_System\wireframe\Schema%20Alignment%20Notes.txt:1>)
- [wireframe/Detailed ASCII Wireframes.txt](</\\?\UNC\192.168.31.6\Austin\Testing\Testing AMS\Nextgen_Assets_Management_System\wireframe\Detailed%20ASCII%20Wireframes.txt:1>)
- [wireframe/Detailed Design Spec.txt](</\\?\UNC\192.168.31.6\Austin\Testing\Testing AMS\Nextgen_Assets_Management_System\wireframe\Detailed%20Design%20Spec.txt:1>)
