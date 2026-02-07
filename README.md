# Content Catalogz

Professional content marketing website with modern design and clean architecture.

## Project Structure

```
content_catalogz/
├── index.html              # Homepage
├── about.html              # About page
├── quote.html              # Quote request page
├── assets/
│   ├── css/
│   │   └── styles.css     # Main stylesheet
│   ├── js/                # JavaScript files
│   └── images/            # Image assets (logo, etc.)
├── admin/                 # Admin panel (requires login)
│   ├── login.php          # Admin login page
│   ├── dashboard.php      # Main admin dashboard
│   ├── config/            # Configuration files
│   │   ├── db.php        # Database connection
│   │   └── auth.php      # Authentication functions
│   ├── api/              # API endpoints
│   │   ├── save_page.php      # Save database pages
│   │   ├── get_page.php       # Get database page
│   │   ├── delete_page.php    # Delete database page
│   │   ├── get_html_files.php # List HTML files
│   │   ├── read_html_file.php # Read HTML file
│   │   ├── save_html_file.php # Save HTML file
│   │   └── logout.php         # Logout endpoint
│   └── setup/            # Setup scripts
│       ├── init_db.php       # Initialize database
│       ├── reset_password.php # Reset admin password
│       └── check_db.sh       # Database diagnostic
├── backups/              # Automatic HTML file backups
├── .devcontainer/        # Dev container configuration
├── README.md             # This file
├── ADMIN_SETUP.md        # Admin setup guide
├── HTML_EDITOR_GUIDE.md  # HTML editor user guide
└── DEVELOPER_GUIDE.md    # Developer documentation
```

## Features

### Front-End
- Dark theme with hot pink accents
- Responsive design
- Clean, modern UI
- Contact/quote request form
- Newsletter subscription
- Blog section
- Free consultation CTA
- Fixed background with scrolling content

### Admin Panel
- **Full-featured admin dashboard** at `/admin/login.php`
- **HTML Page Editor** - Edit your website pages directly through the browser
- **Database Page Management** - Create and manage dynamic content pages
- **Automatic Backups** - Every HTML edit creates a timestamped backup
- User authentication and session management
- Secure password hashing

## Quick Start

### View the Website

Start the PHP development server:
```bash
php -S 0.0.0.0:8083
```

Visit `http://localhost:8083` in your browser.

### Access Admin Panel

1. Navigate to `http://localhost:8083/admin/login.php`
2. Login with default credentials:
   - Username: `admin`
   - Password: `admin123`
3. **Change the password immediately after first login!**

### Edit Your Pages

From the admin dashboard:
- Click **"📝 Edit Pages"** to edit HTML files directly
- Click **"📄 Database Pages"** to manage dynamic content
- All edits create automatic backups in `/backups`

For detailed instructions, see [HTML_EDITOR_GUIDE.md](HTML_EDITOR_GUIDE.md)

## Tech Stack

- HTML5
- CSS3 (Custom properties/variables)
- Mobile-responsive design
- Dev Container support with PHP 8.2, MySQL 8.0, and phpMyAdmin

## Development

This project includes a dev container configuration for consistent development environments.

### Local Development

Simply open the files in a web browser or use a local web server:

```bash
# Using Python
python3 -m http.server 8000

# Using PHP
php -S localhost:8000
```

Then visit `http://localhost:8000` in your browser.

## License

© 2026 Content Catalogz. All rights reserved.
