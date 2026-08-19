# NiveraCloud - Front Panel

A full-featured hosting front panel supporting **Pterodactyl**, **PufferPanel** (Auth2/OAuth2), and **Pelican** panels. Built with PHP 8.1+, MySQL, and a custom MVC framework.

## Features

- **Multi-Panel Support** - Connect to Pterodactyl, PufferPanel, or Pelican hosting panels
- **Auto User Sync** - Automatically creates users on your hosting panel when they sign up
- **Custom Admin Panel** - Full admin dashboard with sidebar navigation
- **Product Management** - Create products with egg/nest mappings, pricing, resource limits
- **Category System** - Organize products into categories
- **Order & Billing** - Paymenter-like billing with balance, coupons, invoices
- **Support Tickets** - Built-in ticket system with departments and priorities
- **Live Server Control** - Start, stop, restart, kill, console, resource monitoring
- **Custom CSS/JS** - Full customization from the admin panel
- **Logo & Branding** - Upload your own logo, set accent colors, dark/light theme
- **AdSense Integration** - Built-in Google AdSense support
- **Announcements** - Post announcements visible to all users
- **Coupon System** - Percentage or fixed amount discounts
- **Activity Log** - Track all admin and user actions
- **Installer Wizard** - 4-step setup process
- **Responsive Design** - Works on desktop, tablet, and mobile

## Requirements

- PHP 8.1+
- MySQL/MariaDB
- cURL, PDO MySQL, JSON, MBString extensions

## Installation

1. Upload all files to your web server
2. Visit `https://yourdomain.com/install`
3. Follow the 4-step installer wizard
4. Delete or rename `install.php` after setup

## Panel Configuration

Go to **Admin > Panel API** to configure your hosting panel:

### Pterodactyl
- Set Panel URL and Application API Key
- Configure Node ID and Nest ID

### PufferPanel
- Enable PufferPanel and set your Panel URL
- Enter Client Token and Client Secret (OAuth2)
- The panel handles token exchange automatically

### Pelican
- Enable Pelican and set Panel URL
- Enter Application API Key

## Directory Structure

```
NiveraCloud/
├── config/          # Configuration
├── app/
│   ├── Core/        # Framework core (Router, Auth, API clients)
│   ├── Controllers/ # MVC Controllers
│   ├── Models/      # Database Models
│   ├── Middleware/   # Auth, Admin, Guest middleware
│   └── Views/       # PHP templates
├── database/        # SQL schema
├── public/          # Web root (index.php, assets, uploads)
└── README.md
```

## License

MIT License - Free for personal and commercial use.
