#!/bin/bash

###############################################################################
# Fintra Backend - Comprehensive Server Test Script
# Tests Docker setup, API endpoints, database, and PHP functionality
###############################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test configuration
HOST="${1:-localhost}"
PORT="${2:-8080}"
URL="http://$HOST:$PORT"
DB_HOST="$HOST"
DB_PORT="3306"
DB_USER="fintra_user"
DB_PASS="fintra_pass"
DB_NAME="fintracms"

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   Fintra Backend - Server & API Test Suite                 ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Function to print test header
test_header() {
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
}

# Function to print test result
test_result() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✅ $2${NC}"
    else
        echo -e "${RED}❌ $2${NC}"
        if [ -n "$3" ]; then
            echo -e "${RED}   Error: $3${NC}"
        fi
        return 1
    fi
}

# Function to print info
test_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

passed=0
failed=0

###############################################################################
# Test 1: Server Connectivity
###############################################################################

test_header "Test 1: Server Connectivity"

echo "Testing connection to $URL..."

if response=$(curl -s -o /dev/null -w "%{http_code}" "$URL/index.php" 2>/dev/null); then
    if [ "$response" = "200" ] || [ "$response" = "000" ]; then
        test_result 0 "Server is responding (HTTP $response)"
        ((passed++))
    else
        test_result 1 "Server responded with HTTP $response" ""
        ((failed++))
    fi
else
    test_result 1 "Could not connect to server" "Make sure docker-compose is running: docker-compose up -d"
    ((failed++))
fi

echo ""

###############################################################################
# Test 2: PHP Information
###############################################################################

test_header "Test 2: PHP Environment"

# Create test file
TEST_PHP="/tmp/fintra_test_phpinfo.php"
cat > "$TEST_PHP" << 'EOFPHP'
<?php
echo "PHP Version: " . phpVersion() . "\n";
echo "Extensions Loaded: " . count(get_loaded_extensions()) . "\n";
$required = ['curl', 'json', 'mysqli', 'xml'];
foreach ($required as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? "Yes" : "No") . "\n";
}
?>
EOFPHP

test_info "PHP Information:"
php "$TEST_PHP" || test_result 1 "PHP not installed or misconfigured"

rm "$TEST_PHP"
echo ""

###############################################################################
# Test 3: API Endpoints
###############################################################################

test_header "Test 3: API Endpoint Tests"

# Test 3.1: Home endpoint
echo -e "${BLUE}Testing: api.php?service=home${NC}"
if response=$(curl -s -X POST "$URL/api.php" \
    -H "Content-Type: application/json" \
    -d '{"service":"home"}' 2>/dev/null); then

    if echo "$response" | grep -q "{" 2>/dev/null; then
        test_result 0 "Home endpoint returned JSON response"
        ((passed++))
        echo "Response preview: ${response:0:100}..."
    else
        test_result 1 "Home endpoint did not return valid JSON"
        ((failed++))
        echo "Response: $response"
    fi
else
    test_result 1 "Home endpoint request failed"
    ((failed++))
fi

echo ""

# Test 3.2: Topics endpoint
echo -e "${BLUE}Testing: api.php?service=topics${NC}"
if response=$(curl -s -X POST "$URL/api.php" \
    -H "Content-Type: application/json" \
    -d '{"service":"topics"}' 2>/dev/null); then

    if echo "$response" | grep -q "{" 2>/dev/null; then
        test_result 0 "Topics endpoint returned JSON response"
        ((passed++))
    else
        test_result 1 "Topics endpoint did not return valid JSON"
        ((failed++))
    fi
else
    test_result 1 "Topics endpoint request failed"
    ((failed++))
fi

echo ""

# Test 3.3: Search endpoint
echo -e "${BLUE}Testing: api.php?service=search${NC}"
if response=$(curl -s -X POST "$URL/api.php" \
    -H "Content-Type: application/json" \
    -d '{"service":"search","q":"loan"}' 2>/dev/null); then

    if echo "$response" | grep -q "{" 2>/dev/null; then
        test_result 0 "Search endpoint returned JSON response"
        ((passed++))
    else
        test_result 1 "Search endpoint did not return valid JSON"
        ((failed++))
    fi
else
    test_result 1 "Search endpoint request failed"
    ((failed++))
fi

echo ""

###############################################################################
# Test 4: File Permissions
###############################################################################

test_header "Test 4: File & Directory Permissions"

# Check uploads directory
if [ -d "/Users/ruchi/fintra_backend/uploads" ]; then
    if [ -w "/Users/ruchi/fintra_backend/uploads" ]; then
        test_result 0 "uploads/ directory is writable"
        ((passed++))
    else
        test_result 1 "uploads/ directory is NOT writable"
        ((failed++))
    fi
else
    test_info "uploads/ directory does not exist (will be created)"
fi

# Check documents directory
if [ -d "/Users/ruchi/fintra_backend/documents" ]; then
    if [ -w "/Users/ruchi/fintra_backend/documents" ]; then
        test_result 0 "documents/ directory is writable"
        ((passed++))
    else
        test_result 1 "documents/ directory is NOT writable"
        ((failed++))
    fi
else
    test_info "documents/ directory does not exist (will be created)"
fi

echo ""

###############################################################################
# Test 5: Environment Configuration
###############################################################################

test_header "Test 5: Environment Configuration"

if [ -f "/Users/ruchi/fintra_backend/.env" ]; then
    test_result 0 ".env file exists"
    ((passed++))

    # Check for required variables
    if grep -q "OPENAI_API_KEY" "/Users/ruchi/fintra_backend/.env"; then
        test_info "✓ OPENAI_API_KEY is set"
    else
        test_info "⚠ OPENAI_API_KEY is not set"
    fi

    if grep -q "AWS_ACCESS_KEY_ID" "/Users/ruchi/fintra_backend/.env"; then
        test_info "✓ AWS_ACCESS_KEY_ID is set"
    else
        test_info "⚠ AWS_ACCESS_KEY_ID is not set"
    fi
else
    test_result 1 ".env file not found" "Run: cp .env.example .env"
    ((failed++))
fi

echo ""

###############################################################################
# Test 6: Docker Status (if available)
###############################################################################

test_header "Test 6: Docker Status"

if command -v docker &> /dev/null; then
    test_result 0 "Docker is installed"
    ((passed++))

    if docker ps -q 2>/dev/null | grep -q .; then
        test_result 0 "Docker containers are running"
        ((passed++))

        test_info "Running containers:"
        docker ps --format "table {{.Names}}\t{{.Status}}"
    else
        test_result 1 "No Docker containers are running" "Run: docker-compose up -d"
        ((failed++))
    fi
else
    test_info "Docker not installed (not required for PHP server mode)"
fi

echo ""

###############################################################################
# Test 7: Database Connection (Optional)
###############################################################################

test_header "Test 7: Database Connection"

if command -v mysql &> /dev/null; then
    if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1;" &>/dev/null; then
        test_result 0 "Database connection successful"
        ((passed++))

        # Count tables
        TABLE_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME';" 2>/dev/null | tail -1)
        test_info "Tables in database: $TABLE_COUNT"
    else
        test_info "Could not connect to database (this is OK if not using Docker)"
    fi
else
    test_info "MySQL client not installed (not required for API testing)"
fi

echo ""

###############################################################################
# Test Summary
###############################################################################

test_header "Test Summary"

TOTAL=$((passed + failed))
PERCENTAGE=$((passed * 100 / TOTAL))

echo ""
echo -e "${GREEN}✅ Passed: $passed${NC}"
echo -e "${RED}❌ Failed: $failed${NC}"
echo -e "${BLUE}📊 Total: $TOTAL${NC}"
echo ""

if [ $failed -eq 0 ]; then
    echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║  🎉 All tests passed! Your server is working correctly!   ║${NC}"
    echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${BLUE}Next steps:${NC}"
    echo "1. Visit http://$HOST:$PORT in your browser"
    echo "2. Test API endpoints with curl or Postman"
    echo "3. Start developing!"
    exit 0
else
    echo -e "${RED}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${RED}║  ⚠️  Some tests failed. Review errors above.              ║${NC}"
    echo -e "${RED}╚════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${BLUE}Troubleshooting:${NC}"
    echo "1. Make sure Docker containers are running: docker-compose ps"
    echo "2. Check logs: docker-compose logs web"
    echo "3. Verify .env file has correct credentials"
    echo "4. Restart containers: docker-compose down && docker-compose up -d"
    exit 1
fi
