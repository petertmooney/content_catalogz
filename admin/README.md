# Content Catalogz Admin Panel

A complete backend admin system for managing pages and content in the Content Catalogz website.

## Features

- **User Authentication**: Secure login system with password hashing (bcrypt)
- **Page Management**: 
  - Create new pages
  - Edit existing pages
  - Delete pages
  - View all pages in a dashboard
- **Page Properties**:
  - Title
  - URL Slug (unique identifier)
  - Content (rich text)
  - Page Type (standard, blog, service, testimonial)
  - Status (draft, published, archived)
  - Timestamps (created/updated)
- **Database**: MySQL backend for persistent data storage
- **Responsive Design**: Works on desktop and mobile devices

## Installation & Setup

### 1. Prerequisites

- PHP 7.4 or higher
- MySQL 8.0 or higher
- Dev container running (with database service)

### 2. Database Configuration

The database credentials are already configured in `config/db.php` to match your dev container:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'petertmooney');
define('DB_PASS', '68086500aA!');
define('DB_NAME', 'Content_Catalogz');
```

### 3. Initial Setup

**Important:** Make sure your dev container is rebuilt and running:
1. Press `Ctrl+Shift+P` (or `Cmd+Shift+P` on Mac)
2. Run: **"Rebuild Container"**
3. Wait for the container to fully start

Once the container is running, initialize the database:

```bash
php /workspaces/content_catalogz/admin/setup/init_db.php
```

This will create:
- `pages` table - stores all page content
- `users` table - stores admin credentials
- Default admin user

**Default admin credentials:**
- **Username**: `admin`
- **Password**: `admin123`

**⚠️ IMPORTANT:** Change the default password after your first login!

**IMPORTANT**: Change the default password immediately after first login!

### 4. Accessing the Admin Panel

1. Navigate to `http://yoursite.com/admin/login.php`
2. Log in with your credentials
3. You'll be redirected to the dashboard at `/admin/dashboard.php`

## File Structure

```
admin/
├── config/
│   ├── db.php          # Database connection and initialization
│   └── auth.php        # Authentication helper functions
├── api/
│   ├── save_page.php   # Create/update pages
│   ├── get_page.php    # Fetch single page data
│   ├── delete_page.php # Delete pages
│   └── logout.php      # Session logout
├── login.php           # Admin login page
└── dashboard.php       # Admin dashboard
```

## Database Schema

### pages Table
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

## Usage

### Creating a Page

1. Click the **"+ Add New Page"** button
2. Fill in the page details:
   - Title: Page name (displayed to users)
   - Slug: URL-friendly identifier (e.g., `about-us`, `contact`)
   - Type: Choose from Standard, Blog Post, Service, or Testimonial
   - Status: Draft or Published
   - Content: Page content/body
3. Click **"Save Page"**

### Editing a Page

1. Find the page in the pages list
2. Click the **"Edit"** button
3. Modify the content as needed
4. Click **"Save Page"**

### Deleting a Page

1. Find the page in the pages list
2. Click the **"Delete"** button
3. Confirm the deletion when prompted

### Page Status Meanings

- **Draft**: Page is not visible to the public
- **Published**: Page is live and visible on the website
- **Archived**: Page is hidden from the main list but preserved in the database

## Security Features

- **Password Hashing**: All passwords are hashed using bcrypt algorithm
- **Prepared Statements**: Protection against SQL injection
- **Session Management**: Secure session handling
- **XSS Protection**: HTML escaping for all user inputs
- **Login Required**: All admin pages require authentication

## Connecting to Frontend

The pages stored in the admin panel can be accessed by the frontend through:

```php
// Example: Fetch a published page
$sql = "SELECT * FROM pages WHERE slug = ? AND status = 'published'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
```

## Troubleshooting

### Connection Failed
- Verify MySQL is running
- Check database credentials in `config/db.php`
- Ensure the database user has proper permissions

### Login Issues
- Clear browser cookies/cache
- Verify the default admin user exists in the database
- Check browser console for errors

### Page Not Saving
- Ensure all required fields are filled
- Check that the slug is unique
- Review error message in the modal

## Future Enhancements

- User role management (editor, contributor, etc.)
- Page templates
- Media library/image uploads
- Page versioning/revision history
- Bulk actions
- Search and filtering
- API endpoints for external access
- Two-factor authentication

## Support

For issues or questions, please refer to the main README.md in the project root.
