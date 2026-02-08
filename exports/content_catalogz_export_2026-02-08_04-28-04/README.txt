# Content Catalogz Export Package
Export Date: 2026-02-08 04:28:04

## Contents
- database.sql: Complete database dump
- website/: All website files
- EXPORT_INFO.json: Export metadata
- README.txt: This file

## Installation Instructions

### 1. Upload Files
Upload the contents of the 'website' folder to your web server's public directory (usually public_html, www, or htdocs)

### 2. Create Database
- Create a new MySQL database on your hosting control panel
- Note down the database name, username, and password

### 3. Import Database
Using phpMyAdmin or MySQL command line:
```
mysql -u username -p database_name < database.sql
```

Or use phpMyAdmin:
- Navigate to your database
- Click "Import"
- Select database.sql
- Click "Go"

### 4. Update Configuration
Edit admin/config/db.php with your database credentials:
```php
$servername = "localhost"; // Usually localhost
$username = "your_db_username";
$password = "your_db_password";
$dbname = "your_db_name";
```

### 5. Set Permissions
```bash
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod 775 assets/images
chmod 775 backups
```

### 6. Test Your Site
Visit your domain to verify everything works correctly.

## Troubleshooting

### Database Connection Issues
- Verify database credentials in admin/config/db.php
- Ensure your database user has proper permissions
- Check that MySQL service is running

### File Permission Issues  
- Ensure writable directories have chmod 775
- Check PHP user has write permissions

### Site Not Loading
- Verify .htaccess file is present
- Check PHP version (requires PHP 7.4 or higher)
- Review server error logs
