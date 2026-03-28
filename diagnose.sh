#!/bin/bash

###############################################################################
# Fintra Backend - Diagnostic Script
# Checks system configuration and identifies issues
###############################################################################

echo "🔍 Fintra Backend - System Diagnostic Tool"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

check() {
    if command -v "$1" &> /dev/null; then
        version=$("$1" --version 2>&1 | head -1)
        echo -e "${GREEN}✅${NC} $1: $version"
        return 0
    else
        echo -e "${RED}❌${NC} $1: Not installed"
        return 1
    fi
}

echo -e "${BLUE}System Information:${NC}"
echo "OS: $(uname -s) $(uname -r)"
echo "Architecture: $(uname -m)"
echo ""

echo -e "${BLUE}Required Tools:${NC}"
check git
check curl
check nano
echo ""

echo -e "${BLUE}Virtualization & Containers:${NC}"
check docker
check docker-compose
echo ""

echo -e "${BLUE}Programming Languages:${NC}"
check php
check python3
check ruby
echo ""

echo -e "${BLUE}Databases:${NC}"
check mysql
check sqlite3
echo ""

echo -e "${BLUE}Project Configuration:${NC}"
if [ -f .env ]; then
    echo -e "${GREEN}✅${NC} .env file exists"
    echo "  Variables defined:"
    grep -v "^#" .env | grep "=" | sed 's/=.*//' | sed 's/^/    /'
else
    echo -e "${RED}❌${NC} .env file not found"
    echo "   Run: cp .env.example .env"
fi
echo ""

if [ -f docker-compose.yml ]; then
    echo -e "${GREEN}✅${NC} docker-compose.yml exists"
else
    echo -e "${RED}❌${NC} docker-compose.yml not found"
fi
echo ""

echo -e "${BLUE}Directory Structure:${NC}"
for dir in apis templates utils conf uploads documents; do
    if [ -d "$dir" ]; then
        count=$(find "$dir" -type f | wc -l)
        echo -e "${GREEN}✅${NC} $dir/ ($count files)"
    else
        echo -e "${YELLOW}⚠️${NC} $dir/ missing"
    fi
done
echo ""

echo -e "${BLUE}Key Files:${NC}"
for file in index.php api.php CLAUDE.md .gitignore; do
    if [ -f "$file" ]; then
        size=$(wc -c < "$file")
        echo -e "${GREEN}✅${NC} $file ($size bytes)"
    else
        echo -e "${RED}❌${NC} $file missing"
    fi
done
echo ""

echo -e "${BLUE}Git Status:${NC}"
if [ -d .git ]; then
    echo -e "${GREEN}✅${NC} Git repository initialized"
    BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null)
    COMMITS=$(git rev-list --count HEAD 2>/dev/null)
    echo "  Branch: $BRANCH"
    echo "  Commits: $COMMITS"
else
    echo -e "${RED}❌${NC} Not a git repository"
fi
echo ""

echo -e "${BLUE}Network Ports:${NC}"
echo "Checking ports..."
for port in 8080 8000 80 3306; do
    if lsof -i ":$port" >/dev/null 2>&1; then
        process=$(lsof -i ":$port" | tail -1 | awk '{print $1}')
        echo -e "${YELLOW}⚠️${NC} Port $port: In use ($process)"
    else
        echo -e "${GREEN}✅${NC} Port $port: Available"
    fi
done
echo ""

echo -e "${BLUE}Docker Status:${NC}"
if command -v docker &> /dev/null; then
    if docker ps -q 2>/dev/null | grep -q .; then
        echo -e "${GREEN}✅${NC} Docker containers running:"
        docker ps --format "  {{.Names}}: {{.Status}}"
    else
        echo -e "${YELLOW}⚠️${NC} No containers running"
        echo "  Start with: docker-compose up -d"
    fi
else
    echo -e "${RED}❌${NC} Docker not installed"
    echo "  Install from: https://www.docker.com/products/docker-desktop"
fi
echo ""

echo -e "${BLUE}Recommendations:${NC}"

issues=0

if ! command -v docker &> /dev/null; then
    echo "  1. Install Docker Desktop for easiest setup"
    echo "     https://www.docker.com/products/docker-desktop"
    ((issues++))
fi

if [ ! -f .env ]; then
    echo "  2. Create .env file: cp .env.example .env"
    ((issues++))
fi

if ! lsof -i ":8080" >/dev/null 2>&1 && ! lsof -i ":8000" >/dev/null 2>&1; then
    if command -v docker &> /dev/null; then
        echo "  3. Start server: docker-compose up -d"
        ((issues++))
    fi
fi

if [ $issues -eq 0 ]; then
    echo -e "${GREEN}✅ All systems ready!${NC}"
else
    echo ""
    echo "Run the above commands to complete setup."
fi

echo ""
echo "For detailed setup guide, see: SETUP_INSTRUCTIONS.md"
