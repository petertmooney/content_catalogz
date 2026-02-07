#!/bin/bash

# Content Catalogz Admin Setup Script
# This script helps set up the admin panel for local testing

echo "================================"
echo "Content Catalogz Admin Setup"
echo "================================"
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP first."
    exit 1
fi

echo "✅ PHP detected"

# Create necessary directories
echo ""
echo "Creating directories..."
mkdir -p admin/config
mkdir -p admin/api
mkdir -p assets/css
mkdir -p assets/js
mkdir -p assets/images

echo "✅ Directories created"

# Display setup information
echo ""
echo "================================"
echo "Setup Complete!"
echo "================================"
echo ""
echo "Next Steps:"
echo ""
echo "1. Configure Database (if needed):"
echo "   - Edit: admin/config/db.php"
echo "   - Update DB_HOST, DB_USER, DB_PASS, DB_NAME"
echo ""
echo "2. Start Your Web Server:"
echo "   - If using PHP built-in server: php -S localhost:8000"
echo "   - Or use Apache/Nginx as configured"
echo ""
echo "3. Access Admin Panel:"
echo "   - URL: http://localhost:8000/admin/login.php"
echo "   - Username: admin"
echo "   - Password: admin_password"
echo ""
echo "4. Change Default Password!"
echo "   - After first login, change the default admin password"
echo ""
echo "For more information, see admin/README.md"
