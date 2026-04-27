# Nextgen Assets Management System

Nextgen Assets Management System is a Laravel + React web application for managing company assets, inventory quantities, staff handovers, returns, stock movements, departments, receivers, users, notifications, and asset lifecycle records.

The system is designed for office and IT teams that need to know:

- what assets the company owns
- how many items are available
- who is holding issued assets
- which department each receiver belongs to
- when stock came in or went out
- which items are low stock, in maintenance, lost, or retired
- what happened in the system through logs and notifications

## Technology Used

- Backend: Laravel 13
- Frontend: React 19, Vite, Tailwind CSS
- Database: MySQL or MariaDB
- Local server: Laravel Artisan
- Recommended local stack on Windows: VS Code + XAMPP

## What You Need Installed

Install these before running the project:

1. XAMPP with PHP 8.3 or newer and MySQL/MariaDB
2. Composer
3. Node.js 20 or newer
4. Git
5. Visual Studio Code

After installing XAMPP, make sure PHP is available in your terminal.

In PowerShell, check:

```powershell
php -v
composer -V
node -v
npm -v
git --version
```

If `php` is not recognized, add your XAMPP PHP folder to your Windows PATH, usually:

```text
C:\xampp\php
```

Then close and reopen VS Code.

## First Time Setup

Open VS Code, then open a new terminal:

```text
Terminal > New Terminal
```

Clone the project:

```powershell
git clone https://github.com/austinkalisik/Nextgen_Assets_Management_System.git
cd Nextgen_Assets_Management_System
```

Open the project in VS Code:

```powershell
code .
```

## Create The Database In XAMPP

1. Open XAMPP Control Panel.
2. Start `MySQL`.
3. Open phpMyAdmin:

```text
http://127.0.0.1/phpmyadmin
```

4. Click `New`.
5. Create a database named:

```text
nextgen_assets
```

You can also run this SQL:

```sql
CREATE DATABASE nextgen_assets;
```

## Create The Environment File

In VS Code terminal:

```powershell
Copy-Item .env.example .env
```

Open `.env` and confirm these values:

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

For most XAMPP installs, the database username is `root` and the password is blank.

## Install Project Files

Run these commands one by one:

```powershell
composer install
npm install
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
php artisan optimize:clear
npm run build
```

If all commands finish without errors, the project is ready.

## Daily Start Commands

Every day, start XAMPP MySQL first.

Then open the project in VS Code and use two terminals.

Terminal 1:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Terminal 2:

```powershell
npm run dev
```

Open the system:

```text
http://127.0.0.1:8000
```

Keep both terminals open while using the system.

Stop the system with:

```text
Ctrl + C
```

## Start On Office LAN

Use this when another computer or phone on the same network needs to open the system.

Find your desktop IP address:

```powershell
ipconfig
```

Look for `IPv4 Address`.

For this project setup, the desktop IP is:

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

Then run:

Terminal 1:

```powershell
npm run serve:lan
```

Terminal 2:

```powershell
npm run dev:lan
```

Open:

```text
http://192.168.31.34:8000
```

If another device cannot open it, allow Windows Firewall access for ports:

```text
8000
5173
```

## Pull Latest Changes

If the project is already cloned and you only want the latest version:

```powershell
git pull origin main
composer install
npm install
php artisan migrate --seed
php artisan optimize:clear
npm run build
```

Then start the app using the daily start commands.

## Login Accounts

After running migrations and seeders, use the seeded accounts.

Common demo password:

```text
password
```

If login details change, check `database/seeders/UserSeeder.php`.

## Main System Modules

Dashboard:

- shows total assets, available items, assigned quantity, maintenance count, low stock count, overdue count, recent assignments, and recent items

Inventory:

- shows available quantity, assigned quantity, managed quantity, unit cost, stock state, supplier, category, and UOM

Items:

- create and manage asset records
- set category, supplier, quantity, unit of measurement, unit cost, status, location, depreciation, and retirement details

Assignments:

- give assets to receivers
- return assets
- review active assignments
- review returned assignments
- report by receiver
- check available vs assigned stock

Receivers:

- manage people who can receive assets
- each receiver belongs to a department
- assignment dropdown uses receiver and department together

Departments:

- manage organizational departments

Suppliers:

- manage asset suppliers

Categories:

- group items such as laptops, printers, accessories, networking, or consumables

Notifications:

- system alerts for asset updates, assignments, returns, low stock, maintenance, and other events

Activity Logs:

- audit trail of important actions

Settings:

- system branding and behavior settings

Users:

- manage login accounts and roles

## Important Business Logic

Available quantity is stored on the item:

```text
items.quantity = available quantity in store
```

Assigned quantity is calculated from active assignments:

```text
active assignments = assignments where returned_at is empty
```

Managed quantity is calculated as:

```text
managed quantity = available quantity + active assigned quantity
```

Example:

```text
Dell Latitude 5440
Available: 5 unit
Assigned: 3 unit
Managed: 8 unit
```

This means 5 units are still in store, 3 units are issued to staff, and the company still manages 8 units in total.

## Receiver And Department Logic

Receivers are separate from departments.

Example:

```text
Receiver: tech support
Department: IT Support
Assignment dropdown: tech support - IT Support
```

To add, edit, or delete receivers:

```text
Administration > Receivers
```

The assignment form only shows active receivers.

## Unit Of Measurement

UOM explains what the quantity means.

Examples:

```text
unit
box
pack
roll
meter
ream
liter
```

For laptops, printers, radios, and switches, use:

```text
unit
```

UOM does not change calculations. It only labels the quantity.

## Stock And Assignment Flow

When an item is created:

1. Item record is created.
2. Opening stock movement is created.
3. Available quantity is set.

When stock is added:

1. Stock IN movement is created.
2. Available quantity increases.

When stock is removed:

1. Stock OUT movement is created.
2. Available quantity decreases.

When an item is assigned:

1. Assignment is created.
2. Stock OUT movement is created.
3. Available quantity decreases.
4. Activity log is created.
5. Notification may be created.

When an item is returned:

1. Assignment gets a return date.
2. Stock IN movement is created.
3. Available quantity increases.
4. Activity log is created.
5. Notification may be created.

## Asset Statuses

Items can have these statuses:

```text
available
maintenance
lost
retired
```

Only available items with quantity above zero can be assigned.

Maintenance, lost, and retired items cannot be assigned.

## Depreciation

The system supports straight-line depreciation for assets.

Required depreciation fields:

- unit cost
- useful life in years
- salvage value
- depreciation start date

Non-depreciable items use:

```text
depreciation_method = none
```

## Run Tests

To verify the system:

```powershell
php artisan test
npm run build
```

Full verification:

```powershell
composer verify
```

## Clear Cache

If the system behaves strangely after changing `.env` or pulling new code:

```powershell
php artisan optimize:clear
```

Then refresh the browser with:

```text
Ctrl + F5
```

## Common Problems And Fixes

Problem: `php` is not recognized.

Fix:

- Add `C:\xampp\php` to Windows PATH.
- Restart VS Code.

Problem: database connection error.

Fix:

- Start MySQL in XAMPP.
- Confirm database `nextgen_assets` exists.
- Confirm `.env` database settings are correct.

Problem: tables are missing.

Fix:

```powershell
php artisan migrate --seed
```

Problem: login does not work after fresh clone.

Fix:

```powershell
php artisan migrate:fresh --seed
```

Warning: this deletes local database data and recreates demo data.

Problem: frontend looks old or blank.

Fix:

```powershell
npm run build
php artisan optimize:clear
```

Then press `Ctrl + F5` in the browser.

Problem: profile photos or uploaded files do not show.

Fix:

```powershell
php artisan storage:link --force
```

## Useful VS Code Tasks

The project includes VS Code tasks in:

```text
.vscode/tasks.json
```

Open:

```text
Ctrl + Shift + P
Tasks: Run Task
```

Useful tasks:

- NextGen: Bootstrap Project
- NextGen: Start App Local
- NextGen: Start App LAN
- NextGen: Verify Project
- NextGen: Clear Laravel Cache

## Files That Should Not Be Uploaded

These are ignored by Git:

- `.env`
- `vendor/`
- `node_modules/`
- `public/build/`
- `public/storage/`
- Laravel logs and cache files

This keeps the GitHub repository clean and safe.

## Project Structure

Important folders:

```text
app/Http/Controllers       Laravel API controllers
app/Models                 Laravel database models
app/Services               Business services
database/migrations        Database table changes
database/seeders           Demo/setup data
resources/js/pages         React pages
resources/js/components    Shared React components
routes/api.php             API routes
routes/web.php             Web routes
tests                      Automated tests
wireframe                  Planning and design documents
```

## GitHub Repository

Repository:

```text
https://github.com/austinkalisik/Nextgen_Assets_Management_System.git
```

Clone:

```powershell
git clone https://github.com/austinkalisik/Nextgen_Assets_Management_System.git
```

Pull latest:

```powershell
git pull origin main
```

## Final Notes

For normal local testing, only XAMPP MySQL is needed. Do not start Apache unless you need phpMyAdmin. The Laravel backend runs through `php artisan serve`, and the React frontend runs through Vite.

