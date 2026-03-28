# Local Development Setup - Fintra Backend

This guide explains how to set up and run the Fintra Backend locally for development and testing.

## Prerequisites

- macOS with Homebrew, Linux, or Windows with WSL
- Docker (recommended for easiest setup)
- OR PHP 7.4+ and MySQL 5.7+
- Git

## Option 1: Docker (Recommended) ⭐

### Quick Start

1. **Install Docker Desktop** from https://www.docker.com/products/docker-desktop

2. **Create `.env` file** from the template:
   ```bash
   cp .env.example .env
   ```
   Edit `.env` and add your actual credentials

3. **Start the containers**:
   ```bash
   docker-compose up -d
   ```

4. **Access the website**:
   - Open browser: http://localhost:8080
   - Database: localhost:3306 (MySQL)

5. **Stop the server**:
   ```bash
   docker-compose down
   ```

### Docker Commands

```bash
# View logs
docker-compose logs -f web

# Execute PHP command in container
docker-compose exec web php -v

# Access MySQL database
docker-compose exec db mysql -u fintra_user -pfintra_pass fintracms
```

---

## Option 2: Using PHP Built-in Server

### Quick Start

1. **Install PHP** (macOS with Homebrew):
   ```bash
   brew install php
   ```

2. **Create `.env` file**:
   ```bash
   cp .env.example .env
   # Edit .env with your credentials
   ```

3. **Start PHP server**:
   ```bash
   php -S localhost:8000 server.php
   ```

4. **Access the website**:
   - Open browser: http://localhost:8000

### Notes

- This uses the built-in router (`server.php`)
- Good for quick development
- **Database**: You'll need MySQL running separately
- Not suitable for production

---

## Option 3: Manual Apache + PHP Setup

### macOS

1. **Install PHP and Apache via Homebrew**:
   ```bash
   brew install php httpd
   ```

2. **Configure Apache**:
   ```bash
   sudo nano /usr/local/etc/httpd/httpd.conf
   ```

   Add/modify these lines:
   ```apache
   LoadModule php_module /usr/local/opt/php/lib/httpd/modules/libphp.so

   <FilesMatch \.php$>
       SetHandler application/x-httpd-php
   </FilesMatch>

   DocumentRoot "/Users/ruchi/fintra_backend"

   <Directory "/Users/ruchi/fintra_backend">
       Options Indexes FollowSymLinks
       AllowOverride All
       Require all granted
   </Directory>
   ```

3. **Start Apache**:
   ```bash
   brew services start httpd
   ```

4. **Access the website**:
   - Open browser: http://localhost

5. **Stop Apache**:
   ```bash
   brew services stop httpd
   ```

### Linux (Ubuntu/Debian)

```bash
# Install dependencies
sudo apt-get update
sudo apt-get install -y apache2 php php-mysql php-curl php-xml

# Enable mod_rewrite
sudo a2enmod rewrite

# Configure Apache
sudo nano /etc/apache2/sites-available/000-default.conf

# Add DocumentRoot and Directory sections (similar to macOS example above)

# Restart Apache
sudo systemctl restart apache2
```

---

## Configuration

### Environment Variables

The application requires these environment variables. Create a `.env` file:

```env
# OpenAI Configuration
OPENAI_API_KEY=your_openai_api_key

# AWS Configuration
AWS_ACCESS_KEY_ID=your_aws_access_key
AWS_SECRET_ACCESS_KEY=your_aws_secret_key
AWS_BUCKET=fintrafiles
AWS_REGION=ap-south-1

# Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASS=password
DB_NAME=fintracms
```

### PHP Configuration

Verify these PHP extensions are enabled:

```bash
php -m | grep -E "(curl|json|mbstring|xml|mysqli)"
```

Required extensions:
- curl
- json
- mbstring
- xml
- mysqli (or pdo_mysql)

Enable missing extensions in `/etc/php.ini` or similar.

---

## Database Setup

### Option A: Using Docker (auto-imported)

The SQL file is automatically imported when running `docker-compose up`.

### Option B: Manual Import

1. **Create database**:
   ```bash
   mysql -u root -p
   CREATE DATABASE fintracms;
   EXIT;
   ```

2. **Import schema**:
   ```bash
   mysql -u root -p fintracms < fintracms.sql
   ```

---

## Testing the Setup

### 1. Test Homepage
```bash
curl http://localhost:8080/
# or http://localhost:8000 (PHP server)
```

### 2. Test API Endpoint
```bash
curl -X POST http://localhost:8080/api.php \
  -H "Content-Type: application/json" \
  -d '{"service":"home"}'
```

### 3. Test PHP Info
Create `phpinfo.php` in the root:
```php
<?php phpinfo(); ?>
```

Then access: http://localhost:8080/phpinfo.php

---

## Troubleshooting

### "502 Bad Gateway" or "Can't connect"

- Check if server is running: `curl http://localhost:8080`
- Check logs: `docker-compose logs web`
- Check port is not in use: `lsof -i :8080`

### "Database connection failed"

- Verify MySQL is running
- Check credentials in `.env`
- Ensure database is initialized

### "PHP extension not found"

- Check installed extensions: `php -m`
- Install missing extension via package manager
- Restart Apache/PHP server

### "Permission denied" on uploads

```bash
chmod -R 755 /Users/ruchi/fintra_backend/uploads
chmod -R 755 /Users/ruchi/fintra_backend/documents
```

---

## API Endpoints to Test

Once running, test these endpoints:

```bash
# Home page data
curl -X POST http://localhost:8080/api.php \
  -H "Content-Type: application/json" \
  -d '{"service":"home"}'

# Topics
curl -X POST http://localhost:8080/api.php \
  -H "Content-Type: application/json" \
  -d '{"service":"topics"}'

# Search
curl -X POST http://localhost:8080/api.php \
  -H "Content-Type: application/json" \
  -d '{"service":"search","q":"loan"}'
```

---

## Development Tips

1. **Enable error reporting**:
   Add to `index.php`:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

2. **Check server status**:
   ```bash
   # Docker
   docker-compose ps

   # Apache
   sudo apachectl status
   ```

3. **View real-time logs**:
   ```bash
   # Docker
   docker-compose logs -f web

   # Apache
   tail -f /var/log/apache2/access.log
   ```

4. **Database client**:
   - Use MySQL CLI: `mysql -u root -p`
   - Or GUI: MySQL Workbench, Sequel Pro, TablePlus

---

## Next Steps

1. Set up environment variables in `.env`
2. Start the server using your preferred method
3. Test API endpoints
4. Configure IDE/editor for PHP development
5. Start making changes to the codebase

Happy coding! 🚀
