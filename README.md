<p align="center">
  <img src="https://mobaro.ir/assets/images/logo.png" alt="Mobaro Logo" width="200"/>
</p>

<h1 align="center">Mobaro — Professional Beauty Salon Management System</h1>

<p align="center">
  <strong>A full-featured PHP MVC platform for beauty salons & spas</strong>
  <br>
  Online booking · E-commerce · Customer dashboard · Admin panel · Blog
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=flat-square&logo=php" alt="PHP Version"/>
  <img src="https://img.shields.io/badge/license-MIT-blue?style=flat-square" alt="License"/>
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql" alt="MySQL"/>
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap" alt="Bootstrap"/>
  <img src="https://img.shields.io/badge/RTL-Persian-27ae60?style=flat-square" alt="RTL Persian"/>
</p>

---

## ✨ Features

| Module | Highlights |
|--------|------------|
| **📅 Online Booking** | Real-time appointment scheduling, service selection, staff assignment, automated reminders |
| **🛍️ E-commerce** | Product catalog, shopping cart, Zarinpal payment gateway, order tracking |
| **👤 Customer Dashboard** | Profile management, appointment history, order history, wishlist, wallet |
| **⚙️ Admin Panel** | Full CRUD for services, products, blog posts, appointments, orders, users, media gallery |
| **📝 Blog** | Persian blog with categories, comments, search, social sharing |
| **🖼️ Media Gallery** | Image & video upload, streaming via media controller, multi-source support |
| **🔒 Security** | CSRF protection, rate limiting, input sanitization, PDO prepared statements, auth middleware |
| **💳 Payment** | Zarinpal payment gateway integration with verification & callback handling |
| **📱 Responsive** | Mobile-first Bootstrap 5.3 UI with RTL Persian support |
| **🚀 Performance** | File-based caching system with tag invalidation, paginated queries, optimized assets |
| **🔍 SEO** | Clean URLs via custom router, meta tags, structured content |

## 🏗️ Architecture

```
├── app/
│   ├── Controllers/        # Application controllers
│   │   ├── AdminController.php
│   │   ├── AuthController.php
│   │   ├── ShopController.php
│   │   ├── BlogController.php
│   │   ├── MediaController.php
│   │   └── ...
│   ├── Models/             # Active record models
│   ├── Services/           # Business logic (ZarinPal, FileUploader, etc.)
│   ├── Middleware/          # Auth, CSRF, rate limiting
│   ├── views/              # PHP templates (dashboard, shop, admin)
│   ├── helpers.php         # Global helper functions
│   ├── Auth.php            # Authentication handler
│   ├── Router.php          # Custom request router
│   ├── Database.php        # PDO database wrapper
│   ├── Cache.php           # File-based cache with tag system
│   └── Config.php          # Configuration manager
├── public/                 # Document root
│   ├── index.php           # Front controller
│   ├── .htaccess           # Apache rewrite rules
│   └── assets/             # CSS, JS, images, uploads
├── tests/                  # PHPUnit test suite
├── phpcs.xml               # PHP_CodeSniffer config (PSR-12)
├── phpunit.xml             # PHPUnit configuration
└── sonar-project.properties # SonarCloud analysis config
```

## 🚀 Getting Started

### Prerequisites

- PHP 8.1 or higher
- MySQL 8.0+
- Apache with `mod_rewrite` enabled
- Composer

### Installation

```bash
git clone https://github.com/Hordekiller/mobaro.git
cd mobaro
composer install
cp .env.example .env
```

Configure your database credentials and other settings in `.env`:

```env
DB_HOST=localhost
DB_NAME=mobaro
DB_USER=root
DB_PASS=your_password
APP_URL=https://mobaro.ir
```

Import the database schema:

```bash
mysql -u root -p mobaro < database/schema.sql
```

Serve the application:

```bash
php -S localhost:8000 -t public/
```

### Development

```bash
# Run linter (PHP_CodeSniffer PSR-12)
composer lint

# Auto-fix lint issues
composer lint:fix

# Run tests
composer test
```

## 🛡️ Security

- **CodeQL** — GitHub Actions workflow for code security analysis (PHP + JavaScript)
- **Dependabot** — Automated dependency updates (composer + GitHub Actions)
- **PHPCS** — PSR-12 coding standard enforcement
- **PHPUnit** — Automated test suite
- **SonarCloud** — Continuous code quality & security inspection
- All SQL queries use prepared statements
- CSRF tokens on all forms
- Rate limiting on auth endpoints
- Input sanitization and output escaping

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing`)
3. Run the linter and tests (`composer lint && composer test`)
4. Commit your changes (`git commit -m 'Add amazing feature'`)
5. Push to the branch (`git push origin feature/amazing`)
6. Open a Pull Request

## 📄 License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

## 🌐 Links

- **Website:** [mobaro.ir](https://mobaro.ir)
- **GitHub:** [github.com/Hordekiller/mobaro](https://github.com/Hordekiller/mobaro)
