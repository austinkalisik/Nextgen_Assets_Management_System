# Nextgen Assets Management System

This is a simple web app for managing office assets like computers, printers, and supplies. It tracks inventory, assignments, and notifications. Built with Laravel (backend) and React (frontend).

## What You Need

Before starting, make sure you have these installed on your computer:

- **PHP 8.3 or newer** (the programming language for the backend)
- **Composer** (tool to install PHP packages)
- **Node.js 20 or newer** (for the frontend)
- **npm** (comes with Node.js, used to install frontend packages)
- **MySQL or MariaDB** (database to store data)
- **Git** (to download the project)

If you're on Windows and using XAMPP, start MySQL first. You don't need Apache because this app runs its own server.

## Step-by-Step Setup

Follow these steps to get the app running. Do them in order.

### 1. Download the Project

Open your terminal (Command Prompt or PowerShell on Windows) and run:

```bash
git clone https://github.com/austinkalisik/Nextgen_Assets_Management_System.git
cd Nextgen_Assets_Management_System
```

This downloads the project and enters its folder.

### 2. Set Up the Database

You need a database to store the app's data.

- Open your MySQL tool (like phpMyAdmin, MySQL Workbench, or command line).
- Create a new database named `nextgen_assets`.

Example SQL command:

```sql
CREATE DATABASE nextgen_assets;
```

### 3. Copy the Settings File

The app needs a settings file. Copy the example file:

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

On Linux/Mac:

```bash
cp .env.example .env
```

### 4. Check Database Settings

Open the `.env` file you just copied (use any text editor like Notepad). Make sure these lines match your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nextgen_assets
DB_USERNAME=root
DB_PASSWORD=
```

- `DB_USERNAME` is usually `root` if you're using XAMPP.
- `DB_PASSWORD` is often blank (empty) for local setups.

### 5. Install Everything

Run this one command to install all needed software and set up the database:

```bash
composer run bootstrap
```

This will:
- Install PHP packages
- Install frontend packages
- Set up the database tables and sample data
- Build the frontend

It might take a few minutes. If it times out, run it again or use the manual steps below.

### Manual Setup (If Bootstrap Fails)

If `composer run bootstrap` doesn't work, do these steps one by one:

```bash
composer install
npm install
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
php artisan optimize:clear
npm run build
```

## Running the App

You have two ways to run the app: for your computer only, or for other devices on your home/office network.

### Option 1: Run on Your Computer Only (Local)

This is the simplest way. The app will only work on the same computer where you run it.

1. Start the backend server:

   ```bash
   php artisan serve --host=127.0.0.1 --port=8000
   ```

2. Open a new terminal and start the frontend:

   ```bash
   npm run dev
   ```

3. Open your web browser and go to:

   ```text
   http://127.0.0.1:8000
   ```

   - `127.0.0.1` means "this computer only"
   - `:8000` is the port number

### Option 2: Run for Other Devices on Your Network (LAN)

This lets you open the app from your phone, tablet, or another computer on the same Wi-Fi.

1. Find your computer's IP address:
   - On Windows: Open Command Prompt and run `ipconfig`. Look for "IPv4 Address" under your Wi-Fi adapter (something like `192.168.1.100`).
   - On Mac/Linux: Run `ifconfig` or `ip addr` and look for your network IP.

2. Update the `.env` file with your IP (replace `192.168.1.100` with your actual IP):

   ```env
   APP_URL=http://192.168.1.100:8000
   VITE_DEV_SERVER_BIND_HOST=0.0.0.0
   VITE_DEV_SERVER_HOST=192.168.1.100
   VITE_DEV_SERVER_PORT=5173
   ```

3. Start the backend for all devices:

   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

4. Open a new terminal and start the frontend for network:

   ```bash
   npm run dev:lan
   ```

5. Open your web browser and go to:

   ```text
   http://192.168.1.100:8000
   ```

   - Replace `192.168.1.100` with your computer's IP.
   - Now you can open this URL from any device on your network.

**Note:** Keep both terminals running. Close them with Ctrl+C when done.

## Demo Accounts

After setup, log in with these accounts:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@nextgen.local` | `password` |
| Asset Officer | `assets@nextgen.local` | `password` |
| ICT Support | `support@nextgen.local` | `password` |
| Operations Manager | `operations@nextgen.local` | `password` |

## Features

- Dashboard with summaries
- Manage assets and inventory
- Assign and return items
- Track stock movements
- User and department management
- Notifications and reports
- PDF exports
- Straight-line depreciation and book value tracking
- ERD, wireframes, and design notes in the `wireframe/` folder

## CRUD Coverage

The system follows a Laravel API plus React frontend CRUD structure.

| Module | Create | Read | Update | Delete | Notes |
| --- | --- | --- | --- | --- | --- |
| Inventory Items | Yes | Yes | Yes | Yes | Includes stock, assignment protection, depreciation, and CSV/PDF reporting |
| Categories | Yes | Yes | Yes | Yes | Shared CRUD page |
| Departments | Yes | Yes | Yes | Yes | Shared CRUD page |
| Suppliers | Yes | Yes | Yes | Yes | Shared CRUD page |
| Users | Yes | Yes | Yes | Yes | Admin-only user management |
| Assignments | Yes | Yes | Return workflow | Protected history | Prevents over-assigning available stock |
| Notifications | System-created | Yes | Read/unread | Yes | Includes filters, unread count, and stats |
| Settings | Admin-managed | Yes | Yes | Limited | Branding and system behavior settings |

Main CRUD files:

- Backend routes: `routes/api.php`
- Controllers: `app/Http/Controllers/`
- Models: `app/Models/`
- Frontend pages: `resources/js/pages/`
- Shared CRUD component: `resources/js/components/CRUDPage.jsx`

## Depreciation

Inventory items support straight-line depreciation with:

- `unit_cost`
- `is_depreciable`
- `depreciation_method`
- `useful_life_years`
- `salvage_value`
- `depreciation_start_date`
- calculated annual/monthly depreciation
- accumulated depreciation
- current book value per unit and total

Coverage is included in `tests/Feature/ItemDepreciationTest.php`.

## Tech Stack

- Backend: Laravel 13, PHP 8.3+
- Frontend: React 19, Vite, Tailwind CSS
- Database: MySQL or MariaDB
- Testing: PHPUnit

## Testing and Verification

Run the full project verification:

```bash
composer verify
```

This runs:

- Laravel Pint style check
- `php artisan test`
- `npm run build`

Tests use SQLite in memory, so they do not require your local MySQL database.

You can also run checks separately:

```bash
vendor/bin/pint --test
php artisan test
npm run build
```

## Docker Quick Start

For advanced users, you can run everything in Docker:

```bash
docker compose up --build
```

Then open `http://127.0.0.1:8000`.

Docker files included:

- `Dockerfile`
- `docker-compose.yml`
- `.env.docker.example`
- `docker/entrypoint.sh`

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

If the app does not load, make sure both Laravel and Vite are running:

```bash
php artisan serve --host=127.0.0.1 --port=8000
npm run dev
```

If login fails, confirm MySQL is running and the database exists:

```bash
php artisan migrate --seed
php artisan optimize:clear
```

If the frontend is blank or old assets appear, clear the browser cache and rebuild:

```bash
npm run build
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
- `wireframe/erd-current.txt`
- `graph.png`
- `wireframe/Schema Alignment Notes.txt`
- `wireframe/Detailed ASCII Wireframes.txt`
- `wireframe/Detailed Design Spec.txt`
