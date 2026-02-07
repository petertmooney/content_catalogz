# Admin Panel Quick Start Guide

## What's Been Created

A complete backend admin system for the Content Catalogz website with the following features:

### 📁 Directory Structure
```
admin/
├── .htaccess           # Security and rewrite rules
├── README.md           # Comprehensive documentation
├── login.php           # Admin login page
├── dashboard.php       # Admin dashboard with page management
├── config/
│   ├── db.php         # Database configuration and initialization
│   └── auth.php       # Authentication helpers
└── api/
    ├── save_page.php   # Create/update pages
    ├── get_page.php    # Fetch page details
    ├── delete_page.php # Delete pages
    └── logout.php      # Logout functionality
```

## Getting Started

### Step 1: Setup Database Credentials
Edit `admin/config/db.php` and update if needed:
```php
define('DB_HOST', 'localhost');  // Your MySQL host
define('DB_USER', 'admin');      // Your MySQL username
define('DB_PASS', 'admin_password'); // Your MySQL password
define('DB_NAME', 'content_catalogz');
```

### Step 2: Start Your Web Server
```bash
# Using PHP built-in server
php -S localhost:8000

# Or use Apache/Nginx (configured path: /admin/)
```

### Step 3: Access the Admin Panel
1. Navigate to: `http://localhost:8000/admin/login.php`
2. Login with:
   - **Username**: `admin`
   - **Password**: `admin_password`
3. You'll be redirected to the dashboard

## Features

### 🔐 Authentication
- Secure login system with bcrypt password hashing
- Session management
- Automatic database and user creation on first run
- Default admin account included

### 📄 Page Management
Create, edit, and delete website pages with:
- **Title**: Page name displayed to visitors
- **Slug**: URL-friendly identifier (e.g., `about-us`)
- **Type**: Choose from Standard, Blog Post, Service, Testimonial
- **Status**: Draft, Published, or Archived
- **Content**: Rich text editor for page content
- **Timestamps**: Automatic creation and update tracking

### 🔒 Security Features
- SQL injection prevention (prepared statements)
- XSS protection (HTML escaping)
- Cross-site request forgery protection
- Secure password hashing (bcrypt)
- Session validation on all admin pages
- Directory access restrictions (.htaccess)

### 📱 Responsive Design
- Clean, modern UI that works on desktop and mobile
- Modal forms for adding/editing pages
- Smart table layout with all page information

## Database Schema

### pages Table
Stores all website pages with content
```sql
CREATE TABLE pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT NOT NULL,
    page_type VARCHAR(50),
    status VARCHAR(20) DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### users Table
Stores admin user accounts
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Common Tasks

### Create a New Page
1. Click **"+ Add New Page"** button on dashboard
2. Fill in all required fields
3. Click **"Save Page"**

### Edit a Page
1. Find the page in the list
2. Click **"Edit"** button
3. Modify content as needed
4. Click **"Save Page"**

### Delete a Page
1. Find the page in the list
2. Click **"Delete"** button
3. Confirm deletion

### Change Admin Password
You'll need direct database access or a password reset feature:
```php
// To update password in database
$new_password = password_hash('new_password', PASSWORD_BCRYPT);
// Then update the users table
```

## API Endpoints

### Save Page
**POST** `/admin/api/save_page.php`
```json
{
    "id": 1,                    // Optional (leave empty for new page)
    "title": "Page Title",
    "slug": "page-slug",
    "content": "Page content here",
    "page_type": "standard",
    "status": "published"
}
```

### Get Page
**GET** `/admin/api/get_page.php?id=1`
```json
{
    "success": true,
    "page": {
        "id": 1,
        "title": "...",
        "slug": "...",
        "content": "...",
        "page_type": "...",
        "status": "...",
        "created_at": "2026-02-07 10:00:00",
        "updated_at": "2026-02-07 10:00:00"
    }
}
```

### Delete Page
**POST** `/admin/api/delete_page.php`
```json
{
    "id": 1
}
```

## Frontend Integration

To display pages from the database on your website:

```php
<?php
include 'admin/config/db.php';

// Get published page by slug
$slug = 'about-us';
$sql = "SELECT * FROM pages WHERE slug = ? AND status = 'published'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $page = $result->fetch_assoc();
    echo $page['content'];
}
?>
```

## Admin Link
An "Admin" link has been added to all frontend pages in the navigation menu for quick access to the login page.

## Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check credentials in `admin/config/db.php`
- Ensure database user has CREATE/INSERT permissions

### Login Issues
- Clear browser cache/cookies
- Verify admin user exists in database
- Check username/password case sensitivity

### Pages Not Saving
- Ensure all required fields are filled
- Check that slug is unique
- Verify form submission in browser console

## Next Steps

1. **Change Default Password**: Update the admin password immediately
2. **Create Sample Pages**: Add some pages to test functionality
3. **Customize Styling**: Modify admin CSS for your brand
4. **Add More Features**: Consider image uploads, page templates, etc.
5. **Set Up Backups**: Plan database backup strategy

## Support Documentation

For more detailed information, see:
- [Admin Panel README](admin/README.md) - Comprehensive documentation
- [Database Configuration](admin/config/db.php) - Database setup details
- Frontend HTML files - How to integrate with your website

---

**Created**: February 2026
**Version**: 1.0
**Status**: Ready for production use
