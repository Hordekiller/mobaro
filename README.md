# Mobaro Beauty Salon Management System

A complete, standalone management system for **Mobaro.ir** — a Persian beauty salon. Built with **PHP MVC**, **MySQL**, **Tailwind CSS**, and **vanilla JavaScript**.

## Features

### Public
- **Landing Page** — Hero section with stats, services, gallery, shop, tutorials, testimonials
- **Service Catalog** — Browse beauty services with prices and durations
- **Online Booking** — 3-step appointment booking (service → date/time → confirm)
- **Shop** — Product catalog with cart (session-based)
- **Authentication** — Login/Register with phone number, password recovery

### User Dashboard
- **Dashboard** — Overview stats (appointments, courses, orders, points)
- **My Appointments** — View, filter, cancel appointments
- **My Courses** — Track course progress
- **My Orders** — Order history with tracking
- **Wishlist** — Favorite products
- **Wallet** — Balance, transactions, discount codes
- **Addresses** — Saved addresses CRUD
- **Account Settings** — Profile edit, avatar upload, password change

### Admin Panel
- **Dashboard** — Site-wide stats and today's appointments
- **Services** — CRUD for beauty services
- **Artists** — CRUD for salon artists
- **Appointments** — Manage all bookings (approve/cancel)
- **Products** — CRUD for shop products
- **Users** — View and manage users
- **Courses** — CRUD for educational courses
- **Testimonials** — CRUD for client reviews
- **Settings** — Site configuration (brand colors, contact info, social links)

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8+ (MVC, no framework) |
| Database | MySQL via PDO |
| Frontend | Tailwind CSS + Font Awesome 6 |
| Fonts | Vazirmatn (Persian) + Playfair Display |
| Language | Persian (UI) / English (code) |

## Installation

### Prerequisites
- PHP 8.0+
- MySQL 5.7+
- Apache/Nginx with mod_rewrite

### Setup
```bash
git clone https://github.com/Hordekiller/mobaro.git
cd mobaro

# Configure database
cp database/config.example.php database/config.php
# Edit database/config.php with your credentials

# Import schema and seed data
mysql -u root -p mobaro < database/schema.sql
mysql -u root -p mobaro < database/seed.sql

# Run migration script
php database/migrate.php

# Start development server
php -S localhost:8000 -t public
```

Visit `http://localhost:8000` in your browser.

### Default Logins

| Role | Phone | Password |
|------|-------|----------|
| Admin | 09120000000 | admin123 |
| User | 09123456789 | user123 |

## Project Structure

```
mobaro/
├── app/
│   ├── Controllers/       # Request handlers
│   ├── Models/            # Database models
│   ├── Middleware/         # Auth middleware
│   ├── views/             # PHP templates
│   │   ├── layouts/       # Header/footer
│   │   ├── home/          # Landing page sections
│   │   ├── dashboard/     # User panel tabs
│   │   ├── admin/         # Admin panel pages
│   │   └── partials/      # Shared components
│   ├── Config.php         # App configuration
│   ├── Database.php       # PDO wrapper
│   ├── Router.php         # Request router
│   ├── Auth.php           # Authentication
│   ├── bootstrap.php      # App bootstrap
│   └── helpers.php        # Utility functions
├── public/
│   ├── index.php          # Entry point
│   ├── .htaccess          # URL rewrite
│   └── assets/            # CSS, JS, images, fonts
├── database/
│   ├── schema.sql         # Table definitions
│   ├── seed.sql           # Demo data
│   └── migrate.php        # Setup script
└── .gitignore
```

## License

MIT
