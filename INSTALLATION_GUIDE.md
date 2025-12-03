# 🚀 MiniMinds Academy - Quick Installation Guide

## 📋 Prerequisites

- **XAMPP** (or similar PHP/MySQL environment)
- **PHP 7.4+** with PDO extension
- **MySQL 5.7+** or MariaDB 10.2+
- **Modern web browser** with JavaScript enabled

## ⚡ 5-Minute Installation

### Step 1: Extract Files
```bash
# Download and extract the ZIP file
# Extract to your XAMPP htdocs directory
# Rename folder to "miniminds-academy"
```

### Step 2: Database Setup
```bash
# Start XAMPP Apache and MySQL services
# Go to http://localhost/phpmyadmin
# Create new database named "miniminds_academy"
# Import the database/miniminds.sql file
```

### Step 3: Access Platform
```bash
# Open browser and go to:
http://localhost/miniminds-academy
```

## 🔑 Default Login Accounts

### Admin Account
- **Username**: `admin`
- **Email**: `admin@miniminds.com`
- **Password**: `admin123`

### Parent Account
- **Username**: `testparent`
- **Email**: `parent@example.com`
- **Password**: `parent123`

### Child Accounts
- **Lucky Kid**: PIN `1234`
- **Smart Kid**: PIN `5678`

## 🛠️ Configuration (Optional)

If you need to customize database settings:

1. Open `includes/config.php`
2. Update these values if needed:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'miniminds_academy');
define('DB_USER', 'root');
define('DB_PASS', ''); // Your MySQL password
```

## 📁 Folder Permissions

Create these writable folders:
```bash
mkdir logs/
mkdir assets/uploads/
chmod 755 logs/
chmod 755 assets/uploads/
```

## ✅ Verification

Test these features:
- ✅ Parent registration and login
- ✅ Child login with PIN
- ✅ Course selection and lesson access
- ✅ Progress tracking
- ✅ Achievement system
- ✅ Dashboard functionality

## 🆘 Troubleshooting

### Database Connection Error
- Check MySQL service is running
- Verify database credentials in config.php
- Ensure database "miniminds_academy" exists

### Permission Errors
- Set proper folder permissions (755 for directories)
- Create logs/ directory if missing
- Check .htaccess file configuration

### Session Issues
- Ensure PHP session path is writable
- Clear browser cookies
- Check session configuration in php.ini

## 🌟 Ready to Go!

Your MiniMinds Academy platform is now live! 🎉

Visit http://localhost/miniminds-academy to start exploring!