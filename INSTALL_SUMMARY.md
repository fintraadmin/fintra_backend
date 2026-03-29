# 🚀 Install MySQL, PHP, Apache - Quick Summary

Two ways to install the local stack on macOS:

---

## ⚡ **Option 1: Automatic Installation (Easiest)**

Run the automated script - it does everything for you!

```bash
cd /Users/ruchi/fintra_backend
bash install-local-stack.sh
```

**What it does:**
✅ Installs MySQL 8.0
✅ Installs PHP 8.1
✅ Installs Apache 2.4
✅ Configures Apache with PHP
✅ Creates database
✅ Sets up Fintra backend
✅ Tests everything

**Time: 5-10 minutes**

---

## 📖 **Option 2: Manual Installation**

Follow the detailed guide:

```bash
open INSTALL_LOCAL_STACK.md
```

Or read it in your editor:
- Step 1: Install MySQL 8.0
- Step 2: Install PHP 8.1
- Step 3: Install Apache 2.4
- Step 4: Configure Apache with PHP
- Step 5: Create MySQL Database
- Step 6: Set Up Fintra Backend
- Step 7: Test Your Setup

**Time: 20-30 minutes**

---

## 🎯 **After Installation**

### Visit Your Website
```bash
# Open in browser
http://localhost/
```

### Test the Setup
```bash
bash test_server.sh
bash test_api.sh
bash diagnose.sh
```

### Test API
```bash
curl -X POST http://localhost/api.php \
  -H "Content-Type: application/json" \
  -d '{"service":"home"}'
```

---

## 🔧 **Service Management**

### Start Services
```bash
brew services start mysql@8.0 php@8.1 httpd
```

### Stop Services
```bash
brew services stop mysql@8.0 php@8.1 httpd
```

### Check Status
```bash
brew services list
```

### View Logs
```bash
# Apache
tail -f /usr/local/var/log/httpd/error_log

# PHP
tail -f /usr/local/var/log/php-fpm.log
```

---

## 🗄️ **Database Access**

### Via Command Line
```bash
mysql -u fintra_user -pfintra_pass fintracms
```

### Database Details
- Host: `localhost`
- User: `fintra_user`
- Password: `fintra_pass`
- Database: `fintracms`

### MySQL Management Commands
```bash
# List databases
mysql -u root -e "SHOW DATABASES;"

# Count tables
mysql -u fintra_user -pfintra_pass fintracms -e "SHOW TABLES;"

# Check users
mysql -u root -e "SELECT User, Host FROM mysql.user;"
```

---

## 🆘 **Troubleshooting**

### "Command not found: mysql"
```bash
# Add to PATH
echo 'export PATH="/usr/local/opt/mysql@8.0/bin:$PATH"' >> ~/.zshrc
source ~/.zshrc
mysql --version
```

### "Port 80 already in use"
```bash
lsof -i :80
kill -9 <PID>
brew services restart httpd
```

### "PHP not working in Apache"
```bash
# Check Apache config
apachectl configtest

# View Apache error log
tail -50 /usr/local/var/log/httpd/error_log

# Restart Apache
brew services restart httpd
```

### "Database connection failed"
```bash
# Check MySQL is running
mysql -u root -e "SELECT 1;"

# Check database exists
mysql -u root -e "SHOW DATABASES;"

# Verify credentials
mysql -u fintra_user -pfintra_pass fintracms -e "SELECT 1;"
```

### "Permission denied for uploads"
```bash
chmod -R 777 /Users/ruchi/fintra_backend/uploads
chmod -R 777 /Users/ruchi/fintra_backend/documents
```

---

## 📊 **File Locations**

- Apache config: `/usr/local/etc/httpd/httpd.conf`
- Apache error log: `/usr/local/var/log/httpd/error_log`
- PHP config: `/usr/local/etc/php/8.1/php.ini`
- PHP-FPM log: `/usr/local/var/log/php-fpm.log`
- MySQL data: `/usr/local/var/mysql/`
- Your website: `/Users/ruchi/fintra_backend/`

---

## ✅ **Verification Checklist**

After running the script or manual steps:

- [ ] `mysql --version` shows MySQL 8.0
- [ ] `php -v` shows PHP 8.1
- [ ] `curl http://localhost` shows Apache page
- [ ] `curl http://localhost/` loads successfully
- [ ] `mysql -u fintra_user -pfintra_pass fintracms -e "SELECT 1;"` works
- [ ] `bash test_server.sh` all tests pass
- [ ] Database tables visible: `mysql -u fintra_user -pfintra_pass fintracms -e "SHOW TABLES;"`

---

## 🎉 **Success!**

Once all checks pass:

```bash
# Visit your website
open http://localhost/

# Test APIs
bash test_api.sh

# View database
mysql -u fintra_user -pfintra_pass fintracms

# Happy coding!
```

---

## 🚀 **Quick Start Commands**

```bash
# Install everything automatically
bash install-local-stack.sh

# Access website
open http://localhost/

# Test setup
bash test_server.sh

# Test APIs
bash test_api.sh

# Access database
mysql -u fintra_user -pfintra_pass fintracms

# View logs
tail -f /usr/local/var/log/httpd/error_log
```

---

**Ready to get started?**

```bash
bash install-local-stack.sh
```

Then: `open http://localhost/` 🎉
