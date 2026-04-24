# Nextgen Assets Management System

Nextgen Assets Management System is a full-stack Laravel and React application for managing office assets, inventory, assignments, stock movements, users, notifications, activity logs, reports, and branding.

The project is designed so another developer can clone it, configure a database, seed demo users, run the app locally, and verify it with automated tests.

## Features

- Dashboard with asset, inventory, assignment, and notification summaries
- Asset and inventory management with stock in, stock out, low stock, and out-of-stock alerts
- Asset assignment and return workflows
- Categories, suppliers, departments, users, roles, and profile management
- In-app notifications with optional email copies
- Activity logs and audit-friendly asset history
- Branding/settings management
- PDF/report support through `barryvdh/laravel-dompdf`
- Docker setup for quick containerized evaluation

## Tech Stack

- Backend: Laravel 13, PHP 8.3+
- Frontend: React 19, Vite, Tailwind CSS
- Database: MySQL or MariaDB
- Testing: PHPUnit with SQLite in-memory test database
- Container option: Docker Compose with MySQL 8

## Requirements

- PHP 8.3 or newer
- Composer 2 or newer
- Node.js 20 or newer
- npm
- MySQL or MariaDB
- Git

For Windows/XAMPP users, start MySQL before running migrations. Apache is optional because local development uses `php artisan serve`.

## Quick Start

Clone the repository:

```bash
git clone https://github.com/austinkalisik/Nextgen_Assets_Management_System.git
cd Nextgen_Assets_Management_System
```

Create a MySQL database named:

```text
nextgen_assets
```

Copy the environment file and update database credentials if needed:

```bash
cp .env.example .env
```

Default local database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nextgen_assets
DB_USERNAME=root
DB_PASSWORD=
```

Run the bootstrap script:

```bash
composer run bootstrap
```

The bootstrap script installs PHP and npm dependencies, creates the app key, creates the storage link, runs migrations and seeders, clears Laravel cache, and builds frontend assets.

## Run Locally

Use two terminals.

Terminal 1:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Terminal 2:

```bash
npm run dev
```

Open:

```text
http://127.0.0.1:8000
```

Do not open the Vite URL directly for normal app usage. Vite serves frontend assets; Laravel serves the application.

## Demo Accounts

After running seeders, use any of these accounts:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@nextgen.local` | `password` |
| Asset Officer | `assets@nextgen.local` | `password` |
| ICT Support | `support@nextgen.local` | `password` |
| Operations Manager | `operations@nextgen.local` | `password` |

## Manual Setup

Use this if you prefer running each step yourself:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
php artisan optimize:clear
npm run build
```

Then start Laravel and Vite as shown in the Run Locally section.

## Testing and Verification

Run the full project verification:

```bash
composer verify
```

This runs:

- `php artisan test`
- `npm run build`

You can also run them separately:

```bash
php artisan test
npm run build
```

Tests use SQLite in memory, so they do not require your local MySQL database.

## Docker Quick Start

This repo includes Docker support:

- `Dockerfile`
- `docker-compose.yml`
- `.env.docker.example`
- `docker/entrypoint.sh`

Run:

```bash
docker compose up --build
```

Open:

```text
http://127.0.0.1:8000
```

Docker notes:

- The app container runs migrations and seeders on startup.
- MySQL runs inside Docker and is exposed to the host on port `3307`.
- Docker uses `.env.docker.example` by default.

## Office LAN Development

For access from other devices on the same network, update `.env` with your machine IP:

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

Open the `APP_URL` value in your browser. Allow Windows Firewall inbound TCP ports `8000` and `5173` if needed.

## VS Code Tasks

The repo includes ready-made tasks in `.vscode/tasks.json`.

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

## Troubleshooting

If login fails, confirm MySQL is running and the database exists:

```bash
php artisan migrate --seed
php artisan optimize:clear
```

If assets do not refresh during development, keep both Laravel and Vite running:

```bash
php artisan serve --host=127.0.0.1 --port=8000
npm run dev
```

If storage files or profile photos do not load:

```bash
php artisan storage:link --force
```

If cached config points to old URLs or database credentials:

```bash
php artisan optimize:clear
```

## Repository Hygiene

Important generated or local-only files are ignored:

- `.env`
- `vendor/`
- `node_modules/`
- `public/build/`
- `public/storage/`
- Laravel logs and caches

Commit source files, migrations, seeders, tests, configuration examples, and documentation. Do not commit real production secrets.

## Production Notes

Before production deployment:

- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Generate and protect a real `APP_KEY`
- Use real database credentials
- Configure mail settings if email notifications are needed
- Serve through HTTPS
- Configure queues, backups, logs, and monitoring
- Review user accounts and seeded demo credentials

## Project References

- `wireframe/ERD - Nextgen Assets Management System.txt`
- `wireframe/Schema Alignment Notes.txt`
- `wireframe/Detailed ASCII Wireframes.txt`
- `wireframe/Detailed Design Spec.txt`
