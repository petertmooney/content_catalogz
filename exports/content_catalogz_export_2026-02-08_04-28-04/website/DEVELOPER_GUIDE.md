# Development & Deployment Guide

## Local Development Setup

### Option 1: Using PHP Built-in Server
```bash
cd /workspaces/content_catalogz
php -S localhost:8000
```
Then access: `http://localhost:8000/admin/login.php`

### Option 2: Using Docker (Recommended)
Create a `docker-compose.yml`:
```yaml
version: '3'
services:
  web:
    image: php:8.1-apache
    ports:
      - "8000:80"
    volumes:
      - .:/var/www/html
    environment:
      MYSQL_HOST: db
      MYSQL_USER: admin
      MYSQL_PASSWORD: admin_password
      MYSQL_DATABASE: content_catalogz

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_DATABASE: content_catalogz
      MYSQL_USER: admin
      MYSQL_PASSWORD: admin_password
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data:
```

Run with:
```bash
docker-compose up -d
```

### Option 3: Using Apache/Nginx
Configure your web server to serve from `/workspaces/content_catalogz` directory with PHP support.

VirtualHost example:
```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /workspaces/content_catalogz
    
    <Directory /workspaces/content_catalogz>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Environment Configuration

### Database Configuration
Edit `admin/config/db.php`:
```php
define('DB_HOST', 'localhost');    // Database host
define('DB_USER', 'admin');        // Database username
define('DB_PASS', 'password');     // Database password
define('DB_NAME', 'content_catalogz');
```

### Admin User Management
Default credentials after setup:
- Username: `admin`
- Password: `admin_password`

To add more admin users (via database):
```php
<?php
$username = 'newuser';
$password = password_hash('newpassword', PASSWORD_BCRYPT);
$email = 'newuser@example.com';

$sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $password, $email);
$stmt->execute();
?>
```

## Project Structure

```
content_catalogz/
├── admin/                    # Admin panel backend
│   ├── config/
│   │   ├── db.php          # Database configuration
│   │   └── auth.php        # Authentication functions
│   ├── api/
│   │   ├── save_page.php   # CRUD save endpoint
│   │   ├── get_page.php    # CRUD get endpoint
│   │   ├── delete_page.php # CRUD delete endpoint
│   │   └── logout.php      # Logout endpoint
│   ├── login.php           # Admin login page
│   ├── dashboard.php       # Admin dashboard
│   ├── .htaccess          # Security rules
│   └── README.md          # Admin documentation
├── assets/                  # Static assets
│   ├── css/
│   │   └── styles.css     # Main stylesheet
│   ├── js/                # JavaScript files
│   └── images/            # Image assets
├── index.html             # Home page
├── about.html            # About page
├── quote.html            # Contact/Quote page
├── ADMIN_SETUP.md        # Admin setup guide
└── README.md             # Main project README
```

## File Descriptions

### admin/config/db.php
- Manages database connection
- Auto-creates database if it doesn't exist
- Auto-creates tables on first run
- Creates default admin user
- Should NOT be publicly accessible

### admin/config/auth.php
- Session management functions
- Login validation helpers
- XSS protection utilities
- Contains: `isLoggedIn()`, `requireLogin()`, `getCurrentUser()`

### admin/login.php
- Public login page
- Username/password authentication
- Session initialization
- Redirects to dashboard on success

### admin/dashboard.php
- Main admin interface
- Displays all pages in a table
- Modal forms for add/edit
- Requires authentication
- Uses AJAX for page operations

### admin/api/save_page.php
- POST endpoint for page CRUD
- Creates or updates pages based on ID
- Input validation
- Slug uniqueness check
- Returns JSON response

### admin/api/get_page.php
- GET endpoint to fetch page data
- Returns page details as JSON
- Used by edit modal to fetch data
- Requires authentication

### admin/api/delete_page.php
- POST endpoint to delete pages
- Soft/hard delete option possible
- Returns JSON success/error
- Requires authentication

## Security Considerations

### HTTPS
Always use HTTPS in production:
```apache
<VirtualHost *:443>
    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/key.pem
    # ... rest of config
</VirtualHost>
```

### Password Security
- Passwords hashed with bcrypt (PASSWORD_BCRYPT)
- Never log or echo passwords
- Enforce strong password requirements
- Consider adding 2FA for production

### Database Security
- Use strong database passwords
- Implement database user with limited permissions
- Regular backups
- SQL injection prevention (prepared statements)
- Consider database encryption at rest

### File Permissions
```bash
# Make config files readable by web server only
chmod 600 admin/config/db.php
chmod 600 admin/config/auth.php

# Restrict admin directory
chmod o-rwx admin/
```

### .htaccess Rules
Already configured to:
- Prevent direct access to config files
- Disable directory listing
- Set security headers (X-Content-Type-Options, X-Frame-Options, X-XSS-Protection)

## Testing

### Manual Testing Checklist
- [ ] Login with default credentials
- [ ] Create a new page with all fields
- [ ] Edit an existing page
- [ ] Delete a page
- [ ] Login with incorrect credentials
- [ ] Verify page status changes (draft/published)
- [ ] Test with special characters in content
- [ ] Test slug uniqueness validation
- [ ] Verify timestamps update correctly
- [ ] Test logout functionality

### Automated Testing
Consider adding:
- PHPUnit for backend testing
- Selenium for UI testing
- SQLite for test database

## Deployment

### Pre-Deployment Checklist
- [ ] Update database credentials
- [ ] Change default admin password
- [ ] Enable HTTPS
- [ ] Set proper file permissions
- [ ] Enable database backups
- [ ] Test all functionality
- [ ] Review security headers
- [ ] Set up error logging

### Production Configuration
```php
// admin/config/db.php changes for production
define('DB_HOST', 'your-production-host');
define('DB_USER', 'production-user');
define('DB_PASS', 'strong-password-here');
define('DB_NAME', 'production_database');

// Consider adding error logging
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php-errors.log');
```

### Backup Strategy
```bash
# Backup database
mysqldump -u admin -p content_catalogz > backup.sql

# Backup files
tar -czf project-backup.tar.gz admin/ assets/ *.html

# Schedule with cron (example)
0 2 * * * /usr/local/bin/backup-script.sh
```

## Monitoring

### Log Files
- PHP error log: `/var/log/php-errors.log`
- Web server access: `/var/log/apache2/access.log`
- Web server errors: `/var/log/apache2/error.log`

### Database Monitoring
```php
// Log all admin actions
$log_sql = "INSERT INTO admin_logs (user_id, action, details, timestamp) VALUES (?, ?, ?, NOW())";
```

## Future Enhancements

1. **User Management**
   - Multiple admin users with roles
   - Permission levels (editor, viewer, admin)
   - User activity logs

2. **Content Features**
   - Rich text editor (Tinymce, CKEditor)
   - Image/file uploads
   - Page revisions/versioning
   - SEO optimization fields

3. **Advanced Admin**
   - Search and filtering
   - Bulk operations
   - Page templates
   - Scheduled publishing

4. **Integration**
   - API for external apps
   - Webhook support
   - Third-party service integration

5. **Performance**
   - Database query optimization
   - Caching strategy
   - Page performance metrics

## Support & Maintenance

### Regular Maintenance
- Monthly: Review logs and security
- Quarterly: Update dependencies
- Semi-annually: Security audit
- Annually: Comprehensive review

### Debugging Tips
```php
// Enable debug mode
define('DEBUG', true);

// Log queries
error_log("SQL: " . $sql);

// Check session
print_r($_SESSION);
```

### Common Issues
**Issue**: Database connection fails
- **Solution**: Check credentials, verify MySQL running

**Issue**: Pages not saving
- **Solution**: Check form submission, review server logs

**Issue**: Admin link not working
- **Solution**: Verify .htaccess and URL structure

---

**Last Updated**: February 2026
**Version**: 1.0
