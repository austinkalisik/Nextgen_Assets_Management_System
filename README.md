#  NextGen Assets Inventory System

A modern **Sales & Inventory Management System** built with Laravel.

---

##  Features

###  Authentication

* Login & Registration system
* Secure password hashing
* Session-based authentication

###  Dashboard

* Total Assets overview
* Total Brands tracking
* Recently added assets
* System summary panel

###  Inventory Management

* Add / Edit / Delete assets
* Track:

  * Part Number
  * Brand
  * Name
  * Description
  * Category
  * Supplier

###  Suppliers

* Add / Delete suppliers
* Link suppliers to products

###  Users

* Manage users
* Edit & delete accounts

###  Categories

* Organize inventory
* Assign categories to items

###  Settings

* Dynamic system name
* Admin email configuration
* Global system control

###  Reports

* System analytics overview

---

##  Tech Stack

* Laravel (PHP Framework)
* MySQL (XAMPP / phpMyAdmin)
* Blade (Templating)
* Tailwind CSS (UI)

---

##  Installation Guide (Step-by-Step)

### 1️ Clone Repository

```bash
git clone https://github.com/austinkalisik/nextgen-assets.git
cd backend
```

---

### 2️ Install Dependencies

```bash
composer install
```

---

### 3️ Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

---

### 4️ Configure Database (XAMPP)

Open `.env` and update:

```env
DB_CONNECTION=mysql
DB_DATABASE=nextgen_assets
DB_USERNAME=root
DB_PASSWORD=
```

 Then create database in phpMyAdmin:

```
nextgen_assets
```

---

### 5️ Run Migrations + Seeders

```bash
php artisan migrate:fresh --seed
```

 This will:

* Create tables
* Insert demo data
* Create admin user

---

### 6️ Start Server

```bash
php artisan serve
```

Visit:

```
http://127.0.0.1:8000
```

---

##  Default Login

```
Email: admin@nextgen.com
Password: password
```

---

##  Project Structure

* Controllers → `app/Http/Controllers`
* Models → `app/Models`
* Views → `resources/views`
* Routes → `routes/web.php`
* Database → `database/migrations`
* Seeders → `database/seeders`

---

##  Notes

* Uses MySQL (XAMPP)
* Fully dynamic system name (Settings module)
* Clean modular structure
* Ready for production upgrade

---

##  Future Improvements

* Role-based access (Admin/User)
* Sales & Payments module
* PDF & CSV reports
* Charts & analytics dashboard

---

Developer

Austin
NextGen Assets System

---

License

MIT License
