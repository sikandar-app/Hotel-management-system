# 🏨 HotelPro - Hotel Management System

A comprehensive, modern hotel management system built with Laravel 10 and Vue.js 3. Manage bookings, rooms, guests, invoices, expenses, and more with an intuitive, user-friendly interface.

## 📋 Table of Contents

- [About](#about)
- [Features](#features)
- [Technologies](#technologies)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [License](#license)

## 🎯 About

HotelPro is a full-stack hotel management application designed to streamline hotel operations. From booking management and room inventory to guest check-ins, invoicing, and expense tracking, HotelPro provides everything needed to manage a hotel efficiently.

Whether you're managing a boutique hotel, resort, or large hospitality facility, HotelPro offers a scalable solution with role-based access control and comprehensive reporting capabilities.

## ✨ Features

### Core Features
- **📅 Booking Management** - Create, update, and manage guest bookings with check-in/check-out dates
- **🛏️ Room Management** - Maintain room inventory with floors, buildings, pricing, and availability status
- **👥 Guest Management** - Track guest information including contact details, identification documents, and booking history
- **🧾 Invoice & Billing** - Generate and manage invoices with automatic calculations
- **💰 Expense Tracking** - Record and categorize operational expenses
- **🔖 Tax Management** - Configure and apply taxes to bookings and services
- **📊 Occupancy Management** - Monitor room occupancy rates and availability
- **⚙️ Settings Management** - Configure system-wide settings and preferences

### Administrative Features
- **🔐 Role-Based Access Control** - Manage permissions with customizable roles
- **👤 User Management** - Create and manage staff accounts with different privilege levels
- **📈 Dashboard** - Visual overview of bookings, occupancy, and key metrics
- **📅 Calendar Integration** - Full calendar view of bookings for easy scheduling
- **📄 PDF Generation** - Export invoices and reports as PDF documents
- **🔔 Notification System** - Get alerts for important booking and operational events

## 🛠️ Technologies

### Backend
- **Framework**: Laravel 10 (PHP 8.1+)
- **Database**: MySQL/MariaDB (configurable)
- **Authentication**: Laravel Sanctum
- **Authorization**: Spatie Laravel Permission
- **PDF Generation**: Barryvdh DOMPDF

### Frontend
- **Framework**: Vue.js 3
- **UI Framework**: Bootstrap 5
- **State Management**: Pinia with Persistedstate Plugin
- **Form Validation**: Vuelidate
- **Calendar**: FullCalendar v6
- **Rich Text Editor**: CKEditor 5
- **Charts**: Chart.js
- **Maps**: Leaflet
- **Notifications**: Vue3 Notification
- **Build Tool**: Vite

### DevTools
- **PHP**: Composer
- **Node.js**: npm/yarn for frontend dependencies
- **Testing**: PHPUnit
- **Code Quality**: Laravel Pint
- **Local Development**: Laravel Sail

## 💻 Requirements

Before you begin, ensure you have:

- **PHP**: 8.1 or higher
- **Composer**: Latest version
- **Node.js**: 14.x or higher
- **npm/yarn**: For frontend package management
- **MySQL/MariaDB**: 5.7 or higher
- **Git**: For version control

## 📦 Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/hotelpro.git
cd hotelpro
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

### Step 3: Install Node.js Dependencies

```bash
npm install
# or
yarn install
```

### Step 4: Environment Setup

```bash
# Copy environment example file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 5: Configure Database

Edit your `.env` file and configure the database connection:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotelpro
DB_USERNAME=root
DB_PASSWORD=
```

### Step 6: Run Migrations

```bash
php artisan migrate
```

### Step 7: Run Seeders (Optional)

```bash
php artisan db:seed
```

This will populate the database with sample data for testing.

### Step 8: Build Frontend Assets

```bash
# Development
npm run dev

# Production
npm run prod
```

### Step 9: Start Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## ⚙️ Configuration

### Application Settings

1. **Database Configuration**: Update `.env` with your database credentials
2. **Mail Configuration**: Configure mail settings in `.env` for email notifications
3. **API Configuration**: Set up API endpoints in `config/api.php`
4. **Payment Gateway** (if applicable): Configure in `config/services.php`

### Quick Reference

| Setting | Location | Default |
|---------|----------|---------|
| Database | `.env` | MySQL |
| Email Driver | `.env` | mail |
| Cache Driver | `config/cache.php` | file |
| Session Driver | `config/session.php` | cookie |

## 🚀 Usage

### Access the Application

1. Navigate to `http://localhost:8000` in your browser
2. Log in with default credentials (if seeders were run)
3. Navigate using the sidebar menu

### Common Tasks

#### Creating a Booking
1. Go to **Bookings** → **New Booking**
2. Fill in guest details (name, contact, identification)
3. Select room and check-in/check-out dates
4. Set pricing and apply discounts if applicable
5. Save booking

#### Managing Rooms
1. Navigate to **Rooms**
2. Add new rooms with floor, building, and pricing information
3. Update room status (available/occupied/maintenance)
4. View occupancy rates

#### Generating Invoices
1. Open a booking
2. Review charges and taxes
3. Click **Generate Invoice**
4. Download as PDF

#### Managing Expenses
1. Go to **Expenses**
2. Create new expense entry
3. Select category and enter amount
4. Track operational costs

## 📁 Project Structure

```
hotelpro/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # API and web controllers
│   │   ├── Middleware/         # HTTP middleware
│   │   └── Resources/          # API resources
│   ├── Models/                 # Eloquent models
│   │   ├── Booking.php
│   │   ├── Room.php
│   │   ├── User.php
│   │   ├── Invoice.php
│   │   ├── Expense.php
│   │   └── ...
│   └── Providers/              # Service providers
├── config/                     # Configuration files
├── database/
│   ├── migrations/             # Database migrations
│   ├── seeders/                # Database seeders
│   └── factories/              # Model factories
├── resources/
│   ├── views/                  # Blade templates
│   ├── js/                     # Vue.js components
│   ├── css/                    # Stylesheets
│   └── images/                 # Static images
├── routes/
│   ├── api.php                 # API routes
│   └── web.php                 # Web routes
├── storage/                    # Uploaded files and logs
├── tests/                      # Test files
├── public/                     # Web root
├── vendor/                     # PHP dependencies (Composer)
├── node_modules/               # Node.js dependencies
├── vite.config.js              # Vite configuration
├── composer.json               # PHP dependencies
├── package.json                # Node.js dependencies
└── artisan                     # Laravel CLI
```

## 🧪 Testing

Run tests with PHPUnit:

```bash
php artisan test
```

Run specific test:

```bash
php artisan test tests/Feature/BookingTest.php
```

## 📚 API Documentation

API endpoints are available at `/api/` with authentication via Laravel Sanctum tokens.

### Key Endpoints
- `GET /api/bookings` - List all bookings
- `POST /api/bookings` - Create new booking
- `GET /api/rooms` - List available rooms
- `GET /api/invoices` - List invoices
- `POST /api/expenses` - Record expense

See API routes in [routes/api.php](routes/api.php) for complete list.

## 🔒 Authentication & Authorization

The application uses:
- **Laravel Sanctum**: For API authentication
- **Spatie Laravel Permission**: For role-based access control

### Default Roles
- Admin - Full system access
- Manager - Booking and room management
- Staff - Limited access for check-ins/check-outs
- Guest - View-only access

## 📝 Database Schema

Key tables:
- `users` - System users and staff
- `bookings` - Guest bookings and reservations
- `rooms` - Room inventory
- `invoices` - Guest invoices and billing
- `expenses` - Operational expenses
- `taxes` - Tax configuration
- `roles` & `permissions` - Access control (Spatie)

## 🐛 Troubleshooting

### Common Issues

**Issue**: Database connection error  
**Solution**: Verify MySQL is running and `.env` database credentials are correct

**Issue**: Permission denied on storage folder  
**Solution**: Run `chmod -R 775 storage/` on Linux/Mac or adjust permissions on Windows

**Issue**: Npm packages not installing  
**Solution**: Delete `node_modules/` and `package-lock.json`, then run `npm install` again

**Issue**: Frontend assets not loading  
**Solution**: Run `npm run dev` for development or `npm run prod` for production

## 📞 Support

For issues and questions:
1. Check the documentation
2. Review existing GitHub issues
3. Create a new issue with detailed description

## 📄 License

This project is licensed under the MIT License. See the LICENSE file for details.

## 🙋 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 👨‍💼 Author

sikandar abbas.

---

**Last Updated**: March 2026  
**Version**: 1.0.0
