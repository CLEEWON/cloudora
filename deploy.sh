#!/bin/bash

# Cloudora Deployment Script

echo "=================================="
echo "Cloudora Cloud Storage Deployment"
echo "=================================="

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   echo "This script should not be run as root"
   exit 1
fi

# Check for required tools
echo "Checking for required tools..."
command -v php >/dev/null 2>&1 || { echo "PHP is required but not installed. Aborting."; exit 1; }
command -v mysql >/dev/null 2>&1 || { echo "MySQL is required but not installed. Aborting."; exit 1; }

# Create uploads directory if it doesn't exist
echo "Setting up uploads directory..."
mkdir -p uploads
chmod 755 uploads

# Set proper permissions
echo "Setting file permissions..."
chmod 644 .htaccess
chmod 644 config.php
chmod 644 auth/loginController.php
chmod 644 auth/formLogin.php
chmod 644 auth/logout.php
chmod 644 auth/session.php
chmod 644 db/database.php

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cat > .env << EOF
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=cloudora
DB_PORT=3306
APP_ENV=development
TIMEZONE=Asia/Jakarta
EOF
    chmod 600 .env
    echo "Created .env file with default values. Please update with your database credentials."
fi

# Check if database exists and import schema if needed
read -p "Do you want to create the database and import schema? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    read -p "Database Host [localhost]: " db_host
    db_host=${db_host:-localhost}

    read -p "Database User [root]: " db_user
    db_user=${db_user:-root}

    read -s -p "Database Password: " db_pass
    echo

    read -p "Database Name [cloudora]: " db_name
    db_name=${db_name:-cloudora}

    # Update config.php with database credentials
    sed -i "s/define('DB_HOST', .*/define('DB_HOST', '$db_host');/" config.php
    sed -i "s/define('DB_USER', .*/define('DB_USER', '$db_user');/" config.php
    sed -i "s/define('DB_PASS', .*/define('DB_PASS', '$db_pass');/" config.php
    sed -i "s/define('DB_NAME', .*/define('DB_NAME', '$db_name');/" config.php

    # Create database and import schema
    mysql -h "$db_host" -u "$db_user" -p"$db_pass" -e "CREATE DATABASE IF NOT EXISTS \`$db_name\`;"
    mysql -h "$db_host" -u "$db_user" -p"$db_pass" "$db_name" < schema.sql

    if [ $? -eq 0 ]; then
        echo "Database created and schema imported successfully!"
    else
        echo "Error importing schema. Please check your database credentials."
        exit 1
    fi
fi

# Create .htaccess for production
if [ ! -f .htaccess ]; then
    echo "Creating .htaccess file..."
    cat > .htaccess << 'EOF'
# Cloudora - Production Security Configuration

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self'  https:; font-src 'self' https://cdn.jsdelivr.net"
</IfModule>

# Prevent access to sensitive files
<Files "config.php">
    Order Allow,Deny
    Deny from all
</Files>

<Files "db/database.php">
    Order Allow,Deny
    Deny from all
</Files>

<FilesMatch "\.(env|sql|log|txt)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Prevent access to uploads directory directly
<Directory "uploads/">
    Order Deny,Allow
    Deny from all
</Directory>

# PHP settings for security
php_flag display_errors Off
php_flag display_startup_errors Off
php_flag log_errors On
php_flag html_errors Off

# File upload restrictions
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
php_value max_input_time 300

# Rewrite engine for clean URLs (if needed)
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
EOF
fi

echo "=================================="
echo "Deployment completed!"
echo "=================================="
echo "Next steps:"
echo "1. Configure your web server to point to this directory"
echo "2. Ensure the web server has write permissions to the uploads/ directory"
echo "3. Visit your domain to access Cloudora"
echo ""
echo "Default login credentials:"
echo "  Admin: admin@cloudora.com / admin123"
echo "  User: user@cloudora.com / user123"
echo "=================================="