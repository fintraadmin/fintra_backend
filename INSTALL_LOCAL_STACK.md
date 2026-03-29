# 🛠️ Install MySQL, PHP, Apache Locally on macOS

Complete guide to set up a local development stack without Docker.

---

## 📋 Prerequisites

- macOS (10.13 or newer)
- Homebrew installed
- Terminal access
- ~30 minutes of setup time

### Install Homebrew (if not already installed)

```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

Verify installation:
```bash
brew --version
```

---

## 1️⃣ **Install MySQL 8.0**

### Step 1: Install MySQL
```bash
brew install mysql@8.0
```

Expected output:
```
==> mysql@8.0 is keg-only...
```

### Step 2: Link MySQL
```bash
brew link mysql@8.0 --force
```

### Step 3: Add MySQL to PATH (Important!)
```bash
echo 'export PATH="/usr/local/opt/mysql@8.0/bin:$PATH"' >> ~/.zshrc
source ~/.zshrc
```

For bash users:
```bash
echo 'export PATH="/usr/local/opt/mysql@8.0/bin:$PATH"' >> ~/.bash_profile
source ~/.bash_profile
```

### Step 4: Start MySQL Service
```bash
brew services start mysql@8.0
```

### Step 5: Verify Installation
```bash
mysql --version
mysql -u root -e "SELECT 1;"
```

You should see: `mysql  Ver 8.0.x for osx64`

### Step 6: Secure MySQL (Optional but Recommended)
```bash
mysql_secure_installation
```

Follow the prompts:
- Enter current password: **Press Enter** (no password set yet)
- Remove anonymous users?: **Y**
- Disable remote login?: **Y**
- Remove test database?: **Y**
- Reload privilege tables?: **Y**

---

## 2️⃣ **Install PHP 8.1**

### Step 1: Add PHP Tap to Homebrew
```bash
brew tap shivammathur/php
```

### Step 2: Install PHP 8.1
```bash
brew install shivammathur/php/php@8.1
```

Expected output: `php@8.1` installed successfully

### Step 3: Link PHP
```bash
brew link php@8.1 --force
```

### Step 4: Add PHP to PATH
```bash
echo 'export PATH="/usr/local/opt/php@8.1/bin:$PATH"' >> ~/.zshrc
source ~/.zshrc
```

For bash users:
```bash
echo 'export PATH="/usr/local/opt/php@8.1/bin:$PATH"' >> ~/.bash_profile
source ~/.bash_profile
```

### Step 5: Verify Installation
```bash
php -v
php -m | grep -E "(curl|json|mysqli|xml)"
```

You should see:
- PHP version info
- List of extensions including curl, json, mysqli, xml

### Step 6: Start PHP-FPM (FastCGI Process Manager)
```bash
brew services start php@8.1
```

### Step 7: Verify PHP is Running
```bash
php -S localhost:8000 -t /Users/ruchi/fintra_backend
```

You should see:
```
[Sat Mar 29 10:00:00 2026] PHP 8.1.0 Development Server
Listening on http://localhost:8000
```

Press `Ctrl+C` to stop.

---

## 3️⃣ **Install Apache 2.4**

### Step 1: Install Apache
```bash
brew install httpd
```

### Step 2: Start Apache Service
```bash
brew services start httpd
```

### Step 3: Verify Apache is Running
```bash
curl http://localhost:80
```

You should see Apache welcome page HTML or "It works!" message.

---

## 4️⃣ **Configure Apache with PHP**

### Step 1: Edit Apache Configuration
```bash
nano /usr/local/etc/httpd/httpd.conf
```

### Step 2: Find and Uncomment PHP Module Line

Look for the line (around line 150):
```apache
#LoadModule php_module lib/httpd/modules/libphp.so
```

**Uncomment it** by removing the `#`:
```apache
LoadModule php_module lib/httpd/modules/libphp.so
```

### Step 3: Add PHP Handler

Find the section `<IfModule dir_module>` and look for:
```apache
DirectoryIndex index.html
```

Change it to:
```apache
DirectoryIndex index.html index.php
```

### Step 4: Add PHP Configuration

Find the end of the file and add:
```apache
<FilesMatch \.php$>
    SetHandler application/x-httpd-php
</FilesMatch>
```

### Step 5: Configure Document Root for Fintra

Find the line:
```apache
DocumentRoot "/usr/local/var/www"
```

Change it to:
```apache
DocumentRoot "/Users/ruchi/fintra_backend"
```

Also find:
```apache
<Directory "/usr/local/var/www">
```

Change it to:
```apache
<Directory "/Users/ruchi/fintra_backend">
```

And change the permissions inside that block:
```apache
<Directory "/Users/ruchi/fintra_backend">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

### Step 6: Save and Exit
- Press `Ctrl+X`
- Type `Y` and press `Enter`
- Press `Enter` again

### Step 7: Test Apache Configuration
```bash
apachectl configtest
```

You should see:
```
Syntax OK
```

### Step 8: Restart Apache
```bash
brew services restart httpd
```

---

## 5️⃣ **Create MySQL Database**

### Step 1: Log into MySQL
```bash
mysql -u root
```

### Step 2: Create Database
```sql
CREATE DATABASE fintracms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fintra_user'@'localhost' IDENTIFIED BY 'fintra_pass';
GRANT ALL PRIVILEGES ON fintracms.* TO 'fintra_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 3: Verify Database Created
```bash
mysql -u fintra_user -pfintra_pass fintracms -e "SHOW TABLES;"
```

Should show empty table list (or tables if schema imported).

---

## 6️⃣ **Set Up Fintra Backend**

### Step 1: Create .env File
```bash
cd /Users/ruchi/fintra_backend
cp .env.example .env
```

### Step 2: Edit .env with Correct Values
```bash
nano .env
```

Change:
```env
DB_HOST=localhost
DB_USER=fintra_user
DB_PASS=fintra_pass
DB_NAME=fintracms
```

Save: `Ctrl+X`, `Y`, `Enter`

### Step 3: Set Permissions
```bash
chmod -R 755 /Users/ruchi/fintra_backend
chmod -R 777 /Users/ruchi/fintra_backend/uploads
chmod -R 777 /Users/ruchi/fintra_backend/documents
```

---

## 7️⃣ **Test Your Setup**

### Test 1: PHP Info
```bash
curl http://localhost/test_phpinfo.php
```

Or create a test file:
```bash
echo '<?php phpinfo(); ?>' > /Users/ruchi/fintra_backend/phpinfo.php
curl http://localhost/phpinfo.php | head -20
```

### Test 2: MySQL Connection
```bash
curl http://localhost/test_db.php
```

Or create test file:
```bash
cat > /Users/ruchi/fintra_backend/test_db.php << 'EOF'
<?php
$conn = new mysqli('localhost', 'fintra_user', 'fintra_pass', 'fintracms');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
echo "✅ Database connected successfully!";
$conn->close();
?>
EOF

curl http://localhost/test_db.php
```

### Test 3: Homepage
```bash
curl http://localhost/index.php | head -20
```

### Test 4: API Endpoint
```bash
curl -X POST http://localhost/api.php \
  -H "Content-Type: application/json" \
  -d '{"service":"home"}'
```

---

## ✅ **Verify Everything is Working**

### Check All Services Running
```bash
# MySQL
mysql -u root -e "SELECT 1;"

# PHP
php -v

# Apache
curl http://localhost -I
```

All should return successful responses.

---

## 🌐 **Access Your Website**

Once everything is set up and running:

**Local Website:** http://localhost/

**Test Database:** http://localhost/test_db.php

**PHP Info:** http://localhost/phpinfo.php

---

## 🔧 **Common Commands**

### Start Services
```bash
brew services start mysql@8.0
brew services start php@8.1
brew services start httpd
```

### Stop Services
```bash
brew services stop mysql@8.0
brew services stop php@8.1
brew services stop httpd
```

### Restart Services
```bash
brew services restart mysql@8.0
brew services restart php@8.1
brew services restart httpd
```

### Check Service Status
```bash
brew services list
```

### View Logs
```bash
# MySQL
tail -f /usr/local/var/mysql/$(hostname).err

# Apache
tail -f /usr/local/var/log/httpd/error_log
tail -f /usr/local/var/log/httpd/access_log

# PHP-FPM
tail -f /usr/local/var/log/php-fpm.log
```

---

## 🧪 **Run Tests**

### Full Diagnostic
```bash
bash diagnose.sh
```

### Test Server
```bash
bash test_server.sh
```

### Test APIs
```bash
bash test_api.sh
```

---

## 🚨 **Troubleshooting**

### MySQL won't start
```bash
# Check if it's already running
lsof -i :3306

# Kill existing process and restart
brew services stop mysql@8.0
rm -rf /usr/local/var/mysql
brew services start mysql@8.0
```

### PHP not in PATH
```bash
# Check PATH
echo $PATH

# Verify shell config
cat ~/.zshrc | grep php

# If missing, add manually:
export PATH="/usr/local/opt/php@8.1/bin:$PATH"
```

### Apache won't start
```bash
# Check config
apachectl configtest

# View errors
cat /usr/local/var/log/httpd/error_log

# Check if port 80 is in use
lsof -i :80
```

### "Permission denied" for uploads
```bash
chmod -R 777 /Users/ruchi/fintra_backend/uploads
chmod -R 777 /Users/ruchi/fintra_backend/documents
```

### Website shows "Forbidden"
```bash
# Check Apache error log
tail -50 /usr/local/var/log/httpd/error_log

# Check file permissions
ls -la /Users/ruchi/fintra_backend/ | head -10

# Fix permissions
chmod 755 /Users/ruchi/fintra_backend
```

### PHP errors not showing
Edit `/usr/local/etc/php/8.1/php.ini`:
```bash
nano /usr/local/etc/php/8.1/php.ini
```

Find and change:
```ini
display_errors = On
error_reporting = E_ALL
```

Restart PHP:
```bash
brew services restart php@8.1
```

---

## 📊 **Verification Checklist**

- [ ] MySQL installed and running
- [ ] PHP installed and running
- [ ] Apache installed and running
- [ ] PHP configured in Apache
- [ ] Database created
- [ ] .env file configured
- [ ] Can access http://localhost
- [ ] Can access http://localhost/api.php
- [ ] Database connection works
- [ ] All tests pass

---

## 🎯 **Next Steps**

1. Follow all 7 steps above
2. Run `bash test_server.sh`
3. Visit http://localhost in browser
4. Test APIs with `bash test_api.sh`
5. Start developing!

---

## 📚 **Useful Resources**

- MySQL Docs: https://dev.mysql.com/doc/
- PHP Docs: https://www.php.net/docs.php
- Apache Docs: https://httpd.apache.org/docs/
- Homebrew: https://brew.sh

---

## 💡 **Pro Tips**

1. **Always restart services** after config changes
2. **Check logs** when something doesn't work
3. **Use specific PHP version** - don't use default system PHP
4. **Set permissions** for uploads/documents directories
5. **Test each component** separately before debugging the whole stack

---

**Estimated time: 20-30 minutes**

Good luck! 🚀
