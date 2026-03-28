# 🚀 Fintra Backend - Local Development Setup

Complete guide to set up and run the Fintra Backend website locally.

## 📋 Quick Overview

| Method | Difficulty | Speed | Requirements |
|--------|-----------|-------|--------------|
| **Docker** | Easy | Fast | Docker Desktop |
| **PHP Built-in Server** | Easy | Fast | PHP + MySQL |
| **Apache** | Medium | Medium | PHP + Apache + MySQL |

---

## ✅ Recommended: Docker (Easiest)

### Step 1: Install Docker

**macOS:**
1. Download Docker Desktop from https://www.docker.com/products/docker-desktop
2. Install and launch it
3. Wait for Docker to start (check the menu bar icon)

**Linux (Ubuntu/Debian):**
```bash
sudo apt-get update
sudo apt-get install docker.io docker-compose
sudo usermod -aG docker $USER
```

**Windows:**
Download and install Docker Desktop from https://www.docker.com/products/docker-desktop

### Step 2: Prepare Environment

```bash
# Navigate to project
cd /Users/ruchi/fintra_backend

# Create .env file with your credentials
cp .env.example .env

# Edit .env with your actual API keys and credentials
nano .env
```

### Step 3: Start the Server

```bash
# Start all services (web + database)
docker-compose up -d

# Wait ~10 seconds for services to start
sleep 10

# Check status
docker-compose ps
```

**Output should show:**
```
NAME            STATUS
fintra_backend  Up 10 seconds
fintra_db       Up 10 seconds
```

### Step 4: Access the Website

Open your browser and visit:
- **Website**: http://localhost:8080
- **Database**: localhost:3306 (MySQL)

### Step 5: Test the API

```bash
# Test homepage endpoint
curl -X POST http://localhost:8080/api.php \
  -H "Content-Type: application/json" \
  -d '{"service":"home"}' | jq

# Test topics endpoint
curl -X POST http://localhost:8080/api.php \
  -H "Content-Type: application/json" \
  -d '{"service":"topics"}' | jq
```

### Useful Docker Commands

```bash
# View logs
docker-compose logs -f web

# Execute command in container
docker-compose exec web php -v

# Stop all services
docker-compose down

# Remove everything (reset database)
docker-compose down -v

# Rebuild containers
docker-compose build --no-cache
```

---

## 📱 Alternative: PHP Built-in Server (Quick Testing)

### Prerequisites

- PHP 7.4 or higher
- MySQL running separately

### Installation on macOS

```bash
# Install PHP (using Homebrew alternatives)
# Option 1: Using Homebrew (if working)
brew install php

# Option 2: Using MacPorts
sudo port install php81 +apache2 +mysql57

# Option 3: Download from https://php.net/downloads.php
```

### Setup & Run

```bash
# Navigate to project
cd /Users/ruchi/fintra_backend

# Create .env file
cp .env.example .env
nano .env  # Edit with your credentials

# Start PHP server
php -S localhost:8000 server.php
```

**Output:**
```
[Fri Mar 28 17:30:00 2026] PHP 8.1.0 Development Server started...
Listening on http://localhost:8000
```

### Access Website

Open browser: http://localhost:8000

**Note:** You'll need MySQL running separately. Start it with:
```bash
# If installed via Homebrew
brew services start mysql

# Or manually
mysql.server start
```

---

## ⚙️ Apache Setup (Production-like)

### macOS Installation

```bash
# Install Apache (built-in on macOS)
# Check if Apache is available
apachectl -v

# Enable Apache
sudo apachectl start

# Edit Apache config
sudo nano /etc/apache2/httpd.conf

# Look for and uncomment these lines:
# LoadModule mpm_prefork_module libexec/apache2/mod_mpm_prefork.so
# LoadModule authz_core_module libexec/apache2/mod_authz_core.so
# LoadModule dir_module libexec/apache2/mod_dir.so
# LoadModule php_module /usr/local/opt/php/lib/httpd/modules/libphp.so

# Add virtual host configuration
sudo nano /etc/apache2/extra/httpd-vhosts.conf
```

Add this to `httpd-vhosts.conf`:
```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot "/Users/ruchi/fintra_backend"

    <Directory "/Users/ruchi/fintra_backend">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

```bash
# Restart Apache
sudo apachectl restart

# Check Apache status
sudo apachectl status
```

### Linux Installation

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install -y apache2 php php-mysql php-curl php-xml

# Enable mod_rewrite
sudo a2enmod rewrite

# Configure virtual host
sudo nano /etc/apache2/sites-available/fintra.conf

# Add the Apache configuration (see above for macOS example)

# Enable site
sudo a2ensite fintra

# Restart Apache
sudo systemctl restart apache2
```

---

## 🗄️ Database Setup

### Docker (Automatic)

Database is automatically created and populated when using `docker-compose up`. Skip to testing.

### Manual Setup

**Create Database:**
```bash
mysql -u root -p
CREATE DATABASE fintracms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON fintracms.* TO 'fintra_user'@'localhost' IDENTIFIED BY 'fintra_pass';
FLUSH PRIVILEGES;
EXIT;
```

**Import Schema:**
```bash
mysql -u fintra_user -pfintra_pass fintracms < fintracms.sql
```

**Verify:**
```bash
mysql -u fintra_user -pfintra_pass fintracms -e "SHOW TABLES;"
```

---

## 🧪 Testing the Setup

### 1. Test Website Loads

```bash
# Using curl
curl -s http://localhost:8080 | head -20

# In browser
http://localhost:8080
```

### 2. Test API Endpoints

```bash
# Home page data
curl -X POST http://localhost:8080/api.php \
  -H "Content-Type: application/json" \
  -d '{"service":"home"}'

# Topics list
curl -X POST http://localhost:8080/api.php \
  -H "Content-Type: application/json" \
  -d '{"service":"topics"}'

# Search
curl -X POST http://localhost:8080/api.php \
  -H "Content-Type: application/json" \
  -d '{"service":"search","q":"loan"}'

# Chapters
curl -X POST http://localhost:8080/api.php \
  -H "Content-Type: application/json" \
  -d '{"service":"chapters"}'
```

### 3. PHP Info

Create `test_phpinfo.php`:
```bash
cat > /Users/ruchi/fintra_backend/test_phpinfo.php << 'EOF'
<?php phpinfo(); ?>
EOF
```

Visit: http://localhost:8080/test_phpinfo.php

### 4. Database Connection Test

Create `test_db.php`:
```bash
cat > /Users/ruchi/fintra_backend/test_db.php << 'EOF'
<?php
// Load database configuration
require 'conf/db.conf';

try {
    $conn = new mysqli(DBHOST, DBUSER, DBPASS, 'fintracms');

    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    echo "✅ Database connected successfully!<br>";
    echo "Database: fintracms<br>";
    echo "Host: " . DBHOST . "<br>";

    // Count tables
    $result = $conn->query("SHOW TABLES;");
    echo "Tables in database: " . $result->num_rows . "<br>";

    $conn->close();
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
EOF
```

Visit: http://localhost:8080/test_db.php

---

## 🔧 Troubleshooting

### "Connection refused" or "Can't connect"

```bash
# Check if server is running
curl -s http://localhost:8080 -w "Status: %{http_code}\n"

# Docker: Check container logs
docker-compose logs web

# Docker: Check if containers are running
docker-compose ps
```

### "Database connection failed"

```bash
# Check if MySQL is running (Docker)
docker-compose exec db mysql -u root -p -e "SELECT 1;"

# Check if MySQL is running (manual)
mysql -u root -p -e "SELECT 1;"

# Verify credentials in .env
cat .env | grep DB_
```

### "Permission denied" for uploads

```bash
# Set correct permissions
chmod -R 755 /Users/ruchi/fintra_backend/uploads
chmod -R 755 /Users/ruchi/fintra_backend/documents
chmod -R 755 /Users/ruchi/fintra_backend/conf
```

### Port already in use

```bash
# Check what's using port 8080
lsof -i :8080

# Kill process using port (replace PID)
kill -9 <PID>

# Or use different port in docker-compose.yml:
# Change "8080:80" to "8081:80"
```

### Memory or timeout errors

Edit `.env` or PHP configuration:
```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 100M
post_max_size = 100M
```

---

## 📂 File Structure Reference

```
/Users/ruchi/fintra_backend/
├── docker-compose.yml       # Docker configuration
├── Dockerfile               # Docker image definition
├── .env.example             # Environment template
├── .env                     # Your credentials (create from template)
├── index.php                # Homepage
├── api.php                  # API router
├── apis/                    # API classes
├── templates/               # Twig templates
├── conf/                    # Configuration files
├── utils/                   # Utility functions
└── uploads/                 # User uploads directory
```

---

## 🎯 Next Steps

1. ✅ Choose setup method (Docker recommended)
2. ✅ Follow installation steps for your method
3. ✅ Create `.env` file with your credentials
4. ✅ Start the server
5. ✅ Test API endpoints using curl examples above
6. ✅ Start developing!

---

## 📚 Useful Resources

- **API Documentation**: See `CLAUDE.md`
- **Architecture Guide**: See `CLAUDE.md`
- **Docker Docs**: https://docs.docker.com/
- **PHP Docs**: https://www.php.net/docs.php
- **MySQL Docs**: https://dev.mysql.com/doc/

---

## ❓ Still Having Issues?

1. Check `LOCAL_SETUP.md` for detailed option-specific guides
2. Review logs: `docker-compose logs -f`
3. Check `.env` file has correct credentials
4. Try the test endpoints in "Testing the Setup" section
5. Run `php -v` to confirm PHP is installed

Happy coding! 🚀
