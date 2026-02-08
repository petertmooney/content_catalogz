#!/bin/bash
# Database Connection Test Script

echo "=================================="
echo "Database Connection Diagnostic"
echo "=================================="
echo ""

# Check if MySQL client is installed
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL client not found"
    echo "   Install it or rebuild your container"
    exit 1
fi
echo "✓ MySQL client is installed"

# Check if MySQL service is accessible
echo ""
echo "Testing MySQL connection..."
if mysql -h 127.0.0.1 -P 3306 -u petertmooney -p'68086500aA!' -e "SELECT 1;" &> /dev/null; then
    echo "✓ MySQL service is running and accessible"
    
    # Check for database
    echo ""
    echo "Checking for Content_Catalogz database..."
    if mysql -h 127.0.0.1 -P 3306 -u petertmooney -p'68086500aA!' -e "USE Content_Catalogz; SELECT 1;" &> /dev/null; then
        echo "✓ Content_Catalogz database exists"
        
        # Check for users table
        echo ""
        echo "Checking for users table..."
        if mysql -h 127.0.0.1 -P 3306 -u petertmooney -p'68086500aA!' Content_Catalogz -e "SELECT COUNT(*) FROM users;" &> /dev/null; then
            echo "✓ Users table exists"
            
            USER_COUNT=$(mysql -h 127.0.0.1 -P 3306 -u petertmooney -p'68086500aA!' Content_Catalogz -e "SELECT COUNT(*) FROM users;" -s -N 2>/dev/null)
            echo "  Found $USER_COUNT user(s)"
            
            echo ""
            echo "=================================="
            echo "✅ Database is ready!"
            echo "=================================="
            echo ""
            echo "You can now access the admin portal at:"
            echo "http://localhost:8083/admin/login.php"
            echo ""
            echo "Default credentials:"
            echo "  Username: admin"
            echo "  Password: admin123"
            echo ""
        else
            echo "❌ Users table not found"
            echo ""
            echo "Run this to initialize the database:"
            echo "  php admin/setup/init_db.php"
        fi
    else
        echo "❌ Content_Catalogz database not found"
        echo ""
        echo "Run this to initialize the database:"
        echo "  php admin/setup/init_db.php"
    fi
else
    echo "❌ Cannot connect to MySQL"
    echo ""
    echo "=================================="
    echo "The database service is not running!"
    echo "=================================="
    echo ""
    echo "To fix this:"
    echo "1. Press Ctrl+Shift+P (or Cmd+Shift+P on Mac)"
    echo "2. Select: 'Rebuild Container'"
    echo "3. Wait 2-3 minutes for the rebuild"
    echo "4. Run this script again"
    echo ""
    exit 1
fi
