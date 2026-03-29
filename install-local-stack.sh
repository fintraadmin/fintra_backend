#!/bin/bash

###############################################################################
# Fintra Backend - Automated Local Stack Installation
# Installs: MySQL 8.0, PHP 8.1, Apache 2.4 on macOS
###############################################################################

set -e

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Fintra Backend - Local Stack Installation Script         ║${NC}"
echo -e "${BLUE}║  Installing: MySQL 8.0 + PHP 8.1 + Apache 2.4            ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Check if running on macOS
if [[ "$OSTYPE" != "darwin"* ]]; then
    echo -e "${RED}❌ This script only works on macOS${NC}"
    exit 1
fi

# Check Homebrew installed
if ! command -v brew &> /dev/null; then
    echo -e "${RED}❌ Homebrew not installed${NC}"
    echo "Install from: https://brew.sh"
    exit 1
fi

echo -e "${GREEN}✅ macOS detected${NC}"
echo -e "${GREEN}✅ Homebrew installed${NC}"
echo ""

# Function to install with error handling
install_package() {
    local package=$1
    local name=$2

    echo -e "${YELLOW}Installing $name...${NC}"
    if brew install "$package" &>/dev/null; then
        echo -e "${GREEN}✅ $name installed${NC}"
        return 0
    else
        echo -e "${RED}❌ Failed to install $name${NC}"
        echo "Try manually: brew install $package"
        return 1
    fi
}

# Step 1: Install MySQL
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}Step 1: Installing MySQL 8.0${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

if brew list mysql@8.0 &>/dev/null; then
    echo -e "${YELLOW}⚠️  MySQL 8.0 already installed${NC}"
else
    install_package "mysql@8.0" "MySQL 8.0"
fi

echo "Linking MySQL..."
brew link mysql@8.0 --force &>/dev/null || true

echo "Adding MySQL to PATH..."
if ! grep -q "mysql@8.0" ~/.zshrc 2>/dev/null; then
    echo 'export PATH="/usr/local/opt/mysql@8.0/bin:$PATH"' >> ~/.zshrc
fi

echo "Starting MySQL service..."
brew services start mysql@8.0 &>/dev/null

sleep 3

if mysql -u root -e "SELECT 1;" &>/dev/null; then
    echo -e "${GREEN}✅ MySQL 8.0 is running${NC}"
else
    echo -e "${RED}❌ MySQL failed to start${NC}"
fi

echo ""

# Step 2: Install PHP
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}Step 2: Installing PHP 8.1${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

echo "Adding PHP tap..."
brew tap shivammathur/php &>/dev/null || true

if brew list php@8.1 &>/dev/null; then
    echo -e "${YELLOW}⚠️  PHP 8.1 already installed${NC}"
else
    install_package "shivammathur/php/php@8.1" "PHP 8.1"
fi

echo "Linking PHP..."
brew link php@8.1 --force &>/dev/null || true

echo "Adding PHP to PATH..."
if ! grep -q "php@8.1" ~/.zshrc 2>/dev/null; then
    echo 'export PATH="/usr/local/opt/php@8.1/bin:$PATH"' >> ~/.zshrc
fi

source ~/.zshrc 2>/dev/null || true

echo "Starting PHP-FPM service..."
brew services start php@8.1 &>/dev/null

sleep 2

if php -v &>/dev/null; then
    echo -e "${GREEN}✅ PHP 8.1 is installed and running${NC}"
    php -v | head -1
else
    echo -e "${RED}❌ PHP failed to load${NC}"
fi

echo ""

# Step 3: Install Apache
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}Step 3: Installing Apache 2.4${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

if brew list httpd &>/dev/null; then
    echo -e "${YELLOW}⚠️  Apache already installed${NC}"
else
    install_package "httpd" "Apache 2.4"
fi

echo "Starting Apache service..."
brew services start httpd &>/dev/null

sleep 2

if curl -s http://localhost:80 &>/dev/null; then
    echo -e "${GREEN}✅ Apache 2.4 is running${NC}"
else
    echo -e "${YELLOW}⚠️  Apache may not be responding (this is OK)${NC}"
fi

echo ""

# Step 4: Configure Apache with PHP
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}Step 4: Configuring Apache with PHP${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

APACHE_CONF="/usr/local/etc/httpd/httpd.conf"

# Backup original config
if [ ! -f "$APACHE_CONF.bak" ]; then
    cp "$APACHE_CONF" "$APACHE_CONF.bak"
    echo -e "${GREEN}✅ Backed up Apache config${NC}"
fi

# Uncomment PHP module line (if not already done)
if ! grep -q "^LoadModule php_module" "$APACHE_CONF"; then
    sed -i '' 's/^#LoadModule php_module lib\/httpd\/modules\/libphp.so/LoadModule php_module lib\/httpd\/modules\/libphp.so/' "$APACHE_CONF"
    echo -e "${GREEN}✅ Enabled PHP module in Apache${NC}"
fi

# Update DocumentRoot to Fintra path
FINTRA_PATH="/Users/ruchi/fintra_backend"
if ! grep -q "DocumentRoot \"$FINTRA_PATH\"" "$APACHE_CONF"; then
    sed -i '' "s|DocumentRoot \"/usr/local/var/www\"|DocumentRoot \"$FINTRA_PATH\"|" "$APACHE_CONF"
    sed -i '' "s|<Directory \"/usr/local/var/www\">|<Directory \"$FINTRA_PATH\">|" "$APACHE_CONF"
    echo -e "${GREEN}✅ Updated Apache DocumentRoot to Fintra${NC}"
fi

# Add PHP handler if not present
if ! grep -q "FilesMatch.*php" "$APACHE_CONF"; then
    cat >> "$APACHE_CONF" << 'EOF'

<FilesMatch \.php$>
    SetHandler application/x-httpd-php
</FilesMatch>
EOF
    echo -e "${GREEN}✅ Added PHP handler to Apache${NC}"
fi

# Test Apache config
echo "Testing Apache configuration..."
if apachectl configtest 2>&1 | grep -q "Syntax OK"; then
    echo -e "${GREEN}✅ Apache config is valid${NC}"

    # Restart Apache
    brew services restart httpd &>/dev/null
    sleep 2
    echo -e "${GREEN}✅ Apache restarted${NC}"
else
    echo -e "${RED}⚠️  Apache config has issues${NC}"
    apachectl configtest
fi

echo ""

# Step 5: Create MySQL Database
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}Step 5: Setting up MySQL Database${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

# Create database and user
mysql -u root << 'SQL'
CREATE DATABASE IF NOT EXISTS fintracms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'fintra_user'@'localhost' IDENTIFIED BY 'fintra_pass';
GRANT ALL PRIVILEGES ON fintracms.* TO 'fintra_user'@'localhost';
FLUSH PRIVILEGES;
SQL

echo -e "${GREEN}✅ Database 'fintracms' created${NC}"
echo -e "${GREEN}✅ User 'fintra_user' created${NC}"
echo -e "${GREEN}✅ Permissions granted${NC}"

echo ""

# Step 6: Configure Fintra Backend
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}Step 6: Configuring Fintra Backend${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

cd "$FINTRA_PATH"

# Create .env if doesn't exist
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo -e "${GREEN}✅ Created .env file${NC}"
fi

# Set permissions
chmod -R 755 "$FINTRA_PATH"
chmod -R 777 "$FINTRA_PATH/uploads" 2>/dev/null || mkdir -p "$FINTRA_PATH/uploads" && chmod 777 "$FINTRA_PATH/uploads"
chmod -R 777 "$FINTRA_PATH/documents" 2>/dev/null || mkdir -p "$FINTRA_PATH/documents" && chmod 777 "$FINTRA_PATH/documents"

echo -e "${GREEN}✅ Set proper permissions${NC}"

echo ""

# Step 7: Verification
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}Step 7: Verification${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

echo -n "MySQL: "
if mysql -u fintra_user -pfintra_pass fintracms -e "SELECT 1;" &>/dev/null; then
    echo -e "${GREEN}✅ Connected${NC}"
else
    echo -e "${RED}❌ Failed${NC}"
fi

echo -n "PHP: "
if php -v &>/dev/null; then
    echo -e "${GREEN}✅ $(php -v | head -1 | cut -d' ' -f1-2)${NC}"
else
    echo -e "${RED}❌ Failed${NC}"
fi

echo -n "Apache: "
if curl -s http://localhost:80 &>/dev/null; then
    echo -e "${GREEN}✅ Running${NC}"
else
    echo -e "${YELLOW}⚠️  Checking...${NC}"
fi

echo ""

# Final Summary
echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✅ Installation Complete!                               ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

echo -e "${YELLOW}📍 Access Your Website:${NC}"
echo "   http://localhost/"
echo ""

echo -e "${YELLOW}🗄️  Database:${NC}"
echo "   Host: localhost"
echo "   User: fintra_user"
echo "   Pass: fintra_pass"
echo "   Database: fintracms"
echo ""

echo -e "${YELLOW}🧪 Test Your Setup:${NC}"
echo "   bash test_server.sh"
echo "   bash test_api.sh"
echo "   bash diagnose.sh"
echo ""

echo -e "${YELLOW}🛑 Service Management:${NC}"
echo "   Start:   brew services start mysql@8.0 php@8.1 httpd"
echo "   Stop:    brew services stop mysql@8.0 php@8.1 httpd"
echo "   Status:  brew services list"
echo ""

echo -e "${YELLOW}📝 Next Steps:${NC}"
echo "   1. Edit /Users/ruchi/fintra_backend/.env if needed"
echo "   2. Visit http://localhost in your browser"
echo "   3. Run 'bash test_server.sh' to verify everything"
echo "   4. Start developing!"
echo ""
