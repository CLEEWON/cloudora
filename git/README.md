# Cloudora - Cloud Storage Application

A secure, modern cloud storage application built with PHP and MySQL. This application allows users to upload, manage, and download files with a beautiful UI.

## Features

- **User Authentication**: Secure login system with role-based access (admin/user)
- **File Management**: Upload, download, and delete files
- **File Security**: Protected file access with database tracking
- **Responsive UI**: Modern, sleek interface with dark theme
- **File Type Support**: Supports various file formats (images, documents, archives, etc.)
- **Search Functionality**: Search through your uploaded files
- **File Information**: View file size and upload date

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache or Nginx web server
- Composer (optional, for dependency management)

## Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd cloudora
```

### 2. Database Setup

Create a MySQL database and import the schema:

```sql
CREATE DATABASE cloudora;
USE cloudora;
SOURCE schema.sql;
```

### 3. Configuration

Update the database configuration in `config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'cloudora');
```

Or use environment variables:

```bash
export DB_HOST=localhost
export DB_USER=your_db_user
export DB_PASS=your_db_password
export DB_NAME=cloudora
```

### 4. Web Server Configuration

Make sure your web server points to the project root directory.

For Apache, ensure `.htaccess` rules are enabled.

For Nginx, add these rules to your configuration:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass php-fpm;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}
```

## Environment Variables

You can configure the application using environment variables:

- `DB_HOST` - Database host (default: localhost)
- `DB_USER` - Database user (default: root)
- `DB_PASS` - Database password (default: empty)
- `DB_NAME` - Database name (default: cloudora)
- `DB_PORT` - Database port (default: 3306)
- `APP_ENV` - Application environment (development/production)
- `TIMEZONE` - PHP timezone (default: Asia/Jakarta)

## Security Features

- Session security with HTTP-only cookies
- SQL injection prevention with prepared statements
- File upload validation and security checks
- Directory traversal prevention
- Password hashing with bcrypt
- Security headers in .htaccess

## API Endpoints

- `GET /` - Home page
- `GET /auth/formLogin.php` - Login page
- `POST /auth/loginController.php` - Login processing
- `GET /auth/logout.php` - Logout
- `GET /views/halamanDashboard.php` - User dashboard
- `POST /upload.php` - File upload
- `GET /download.php` - File download
- `POST /delete.php` - File deletion

## Default Credentials

After importing the schema, you'll have these default accounts:

**Admin:**
- Email: admin@cloudora.com
- Password: admin123

**User:**
- Email: user@cloudora.com
- Password: user123

## Deployment

### For Production

1. Set `APP_ENV` to `production`
2. Enable HTTPS
3. Configure proper error logging
4. Set up a cron job for cleanup tasks if needed
5. Use a reverse proxy (nginx) in front of Apache if desired

### Docker (Coming Soon)

A Docker configuration will be available for easier deployment.

## File Storage

Uploaded files are stored in the `/uploads/` directory. The application tracks file information in the database but serves files securely through PHP to prevent direct access.

## Troubleshooting

- If uploads fail, check file permissions on the `uploads/` directory
- Ensure your PHP configuration allows file uploads
- Check database connection settings in `config.php`
- Verify .htaccess rules are working

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For support, please open an issue in the repository or contact the development team.