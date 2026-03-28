#!/bin/bash

###############################################################################
# Fintra Backend - PHP Built-in Server Setup
# Alternative to Docker - Use for quick testing and development
###############################################################################

echo "🚀 Fintra Backend - PHP Built-in Server Setup"
echo "=============================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Check PHP is installed
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP is not installed${NC}"
    echo ""
    echo "Install PHP using one of these methods:"
    echo ""
    echo "1. Using Homebrew (recommended):"
    echo "   brew install php"
    echo ""
    echo "2. Using MacPorts:"
    echo "   sudo port install php81"
    echo ""
    echo "3. Download from:"
    echo "   https://www.php.net/downloads.php"
    echo ""
    echo "4. Or use Docker instead (easiest):"
    echo "   docker-compose up -d"
    echo ""
    exit 1
fi

echo -e "${GREEN}✅ PHP is installed${NC}"
php -v | head -1
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}⚠️  .env file not found${NC}"
    echo "Creating .env from template..."
    cp .env.example .env
    echo -e "${GREEN}✅ .env created${NC}"
    echo ""
    echo "Edit .env with your credentials:"
    echo "  nano .env"
    echo ""
fi

# Check for MySQL
echo -e "${BLUE}Database Setup:${NC}"
if command -v mysql &> /dev/null; then
    echo -e "${GREEN}✅ MySQL client installed${NC}"

    # Check if MySQL is running
    if mysql -u root -e "SELECT 1;" &>/dev/null 2>&1; then
        echo -e "${GREEN}✅ MySQL is running${NC}"
    else
        echo -e "${YELLOW}⚠️  MySQL may not be running${NC}"
        echo "Start MySQL with:"
        echo "  mysql.server start"
        echo ""
    fi
else
    echo -e "${YELLOW}⚠️  MySQL client not installed${NC}"
    echo "Install with: brew install mysql"
    echo ""
fi

echo ""
echo -e "${BLUE}Starting PHP Development Server:${NC}"
echo ""
echo "Server will run at: http://localhost:8000"
echo "Document root: $(pwd)"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Start PHP server
php -S localhost:8000 server.php
