# PW-Final-Project-PHP (Online Bookstore)

Complete online bookstore with user authentication, shopping cart, and admin panel built with PHP and MySQL.

## Requirements

- PHP 7.4+
- MySQL 5.7+ or MariaDB
- Web server (Apache/XAMPP)

## Installation

1. Clone the repository
2. Create MySQL database: `ksiegarnia_online`
3. Copy `includes/config.example.php` to `includes/config.php`
4. Edit `includes/config.php` with your database credentials
5. Run on your web server

## Features

### Customer Features
- Browse books by category
- Shopping cart
- User registration and login
- Place orders
- Order history

### Admin Panel (`/admin`)
- Manage books (CRUD)
- Manage categories and authors
- Manage users
- View and process orders

## Files

- `index.php` - Homepage
- `ksiazka.php` - Book details
- `kategorie.php` - Categories
- `koszyk.php` - Shopping cart
- `login.php` - User login
- `rejestracja.php` - User registration
- `zamowienie.php` - Place order
- `moje_zamowienia.php` - Order history
- `admin/` - Admin panel
- `includes/config.example.php` - Configuration template
- `includes/header.php` - Page header
- `includes/footer.php` - Page footer
- `style.css` - Styles
