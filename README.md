# Nextgen Assets Management System

Nextgen Assets Management System is a Laravel + React application for managing inventory items, asset assignments, returns, departments, receivers, suppliers, users, notifications, activity logs, depreciation, and stock movement.

The system is owned by Nextgen Technology and is designed for office and IT teams that need to know what assets exist, how many are available, who is holding them, and which department each receiver belongs to.

## Technology

- Backend: Laravel 13
- Frontend: React 19, Vite, Tailwind CSS
- Database: MySQL or MariaDB
- Recommended Windows stack: XAMPP, Composer, Node.js, Git, VS Code

## Requirements

Install these first:

- PHP 8.3 or newer
- Composer
- Node.js 20 or newer
- MySQL or MariaDB
- Git

On Windows with XAMPP, add this folder to your PATH if `php` is not recognized:

```text
C:\xampp\php
```

Check your tools:

```powershell
php -v
composer -V
node -v
npm -v
git --version
```

## Clone And First Setup

```powershell
git clone https://github.com/austinkalisik/Nextgen_Assets_Management_System.git
cd Nextgen_Assets_Management_System
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
php artisan storage:link
```

Create a MySQL/MariaDB database:

```sql
CREATE DATABASE nextgen_assets;
```

Set `.env` database values:

```env
APP_NAME="Nextgen Assets Management System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nextgen_assets
DB_USERNAME=root
DB_PASSWORD=
```

For most XAMPP installs, `root` with a blank password is correct.

Run database setup:

```powershell
php artisan migrate --seed
php artisan optimize:clear
npm run build
```

## Run On Localhost With Artisan And Vite

Use this for daily development.

Start MySQL first. Then open two terminals in the project folder.

Terminal 1:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Terminal 2:

```powershell
npm run dev
```

Open:

```text
http://127.0.0.1:8000
```

Stop both terminals with `Ctrl + C`.

## Run On LAN From A Desktop Server

Use this when other computers or phones on the same network need to open the system from your desktop.

Find your desktop IP:

```powershell
ipconfig
```

Example IP:

```text
192.168.31.34
```

Update `.env`:

```env
APP_URL=http://192.168.31.34:8000
VITE_DEV_SERVER_BIND_HOST=0.0.0.0
VITE_DEV_SERVER_HOST=192.168.31.34
VITE_DEV_SERVER_PORT=5173
```

Terminal 1:

```powershell
npm run serve:lan
```

Terminal 2:

```powershell
npm run dev:lan
```

Open from the desktop or another device on the same network:

```text
http://192.168.31.34:8000
```

If another device cannot connect, allow Windows Firewall access for ports `8000` and `5173`.

## Run With XAMPP Apache Or Another Desktop Web Server

Use this when you want Apache/Nginx to serve the Laravel app instead of `php artisan serve`.

1. Build the frontend:

```powershell
npm run build
```

2. Point the web server document root to the Laravel `public` folder, not the project root.

Example XAMPP Apache virtual host:

```apache
<VirtualHost *:80>
    ServerName nextgen-assets.local
    DocumentRoot "A:/NextGen Projects/ASM/Nextgen_Assets_Management_System/public"

    <Directory "A:/NextGen Projects/ASM/Nextgen_Assets_Management_System/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. Add a hosts entry if using `nextgen-assets.local`:

```text
127.0.0.1 nextgen-assets.local
```

4. Set `.env`:

```env
APP_URL=http://nextgen-assets.local
```

5. Restart Apache and open:

```text
http://nextgen-assets.local
```

For a plain localhost Apache setup, the rule is the same: the web server must serve the `public` folder.

## Pull Latest Changes

If the project is already cloned:

```powershell
git pull origin main
composer install
npm install
php artisan migrate --seed
php artisan optimize:clear
npm run build
```

Then start the app with either the Artisan/Vite workflow or the desktop web server workflow above.

## Login Accounts

Seeded accounts are created by `php artisan migrate --seed`.

Common demo password:

```text
password
```

Check login emails in:

```text
database/seeders/UserSeeder.php
```

## Main Modules

- Dashboard: system overview, metrics, quick actions, recent assignments
- Inventory: item records, stock quantity, lifecycle status, depreciation, purchase date
- Assignments: give out assets, mark returns, people reports, stock checks, history exports
- Departments: office/business units for receivers and reports
- Receivers: people or teams that receive assets; each receiver belongs to a department
- Suppliers: vendor records
- Categories: inventory grouping
- Users: login accounts and roles
- Notifications: user-facing alerts
- Activity Logs: audit trail with filtered CSV/PDF exports
- Settings: branding and system behavior
- Profile: current user account details
- User Guide: in-app plain-language help

## Current Role Types

- Admin: full system access
- Manager: oversees operations
- Asset Officer: manages inventory and assignments
- Procurement Officer: manages suppliers and stock intake work
- Auditor: read-only audit/report access
- Staff: standard user access

## Important Business Logic

Available quantity is stored on the item:

```text
items.quantity = available quantity in store
```

Assigned quantity is calculated from active assignments:

```text
active assignments = assignments where returned_at is empty
```

Managed quantity:

```text
managed quantity = available quantity + active assigned quantity
```

Receiver and department flow:

```text
Department -> Receiver -> Assignment
```

This means the assignment screen should make it clear which department the receiving person belongs to.

## Inventory Tracking

Bulk stock:

```text
One inventory row can represent many units.
```

Serialized asset:

```text
One unique asset, normally quantity 1, with serial number or asset tag.
```

## Depreciation

The system supports straight-line depreciation for fixed assets.

Fields:

- unit cost
- useful life in years
- salvage value
- depreciation start date

Date fields use `YYYY-MM-DD`.

## Verify The Project

Run:

```powershell
php artisan test
npm run build
```

Full verification:

```powershell
composer verify
```

## Clear Cache

Run this after changing `.env`, pulling new code, or seeing stale behavior:

```powershell
php artisan optimize:clear
```

Then hard-refresh the browser with `Ctrl + F5`.

## Common Fixes

If tables are missing:

```powershell
php artisan migrate --seed
```

If you want to reset demo data:

```powershell
php artisan migrate:fresh --seed
```

Warning: this deletes local database data.

If uploaded files or profile photos do not show:

```powershell
php artisan storage:link --force
```

If the frontend looks old:

```powershell
npm run build
php artisan optimize:clear
```

## VS Code Tasks

Available tasks:

- NextGen: Bootstrap Project
- NextGen: Start App Local
- NextGen: Start App LAN
- NextGen: Verify Project
- NextGen: Clear Laravel Cache

Open them with:

```text
Ctrl + Shift + P
Tasks: Run Task
```

## Files Not Committed

These are intentionally ignored:

- `.env`
- `vendor/`
- `node_modules/`
- `public/build/`
- `public/storage/`
- Laravel logs and cache files

## Project Structure

```text
app/Http/Controllers       Laravel API controllers
app/Models                 Laravel models
app/Services               Business services
database/migrations        Database schema
database/seeders           Demo/setup data
resources/js/pages         React pages
resources/js/components    Shared React components
routes/api.php             API routes
routes/web.php             Web routes
tests                      Automated tests
wireframe                  Planning and design documents
```

## GitHub

Repository:

```text
https://github.com/austinkalisik/Nextgen_Assets_Management_System.git
```

Clone:

```powershell
git clone https://github.com/austinkalisik/Nextgen_Assets_Management_System.git
```

Pull:

```powershell
git pull origin main
```
