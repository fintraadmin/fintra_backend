#!/bin/bash

###############################################################################
# Fintra Backend - API Endpoint Test Script
# Tests all major API endpoints with detailed output
###############################################################################

HOST="${1:-localhost}"
PORT="${2:-8080}"
URL="http://$HOST:$PORT"

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   Fintra Backend - API Endpoint Tests                      ║${NC}"
echo -e "${BLUE}║   Testing: $URL${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Function to test API endpoint
test_endpoint() {
    local name="$1"
    local service="$2"
    local data="$3"

    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}Testing: $name${NC}"
    echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

    if [ -z "$data" ]; then
        data="{\"service\":\"$service\"}"
    else
        data="{\"service\":\"$service\",$data}"
    fi

    echo "Request:"
    echo "  POST $URL/api.php"
    echo "  Content-Type: application/json"
    echo "  Body: $data"
    echo ""
    echo "Response:"

    response=$(curl -s -X POST "$URL/api.php" \
        -H "Content-Type: application/json" \
        -d "$data" 2>/dev/null)

    # Try to format with jq if available, otherwise show raw
    if command -v jq &> /dev/null; then
        echo "$response" | jq . 2>/dev/null || echo "$response"
    else
        echo "$response"
    fi

    if echo "$response" | grep -q "{" 2>/dev/null; then
        echo -e "${GREEN}✅ Valid JSON response${NC}"
    else
        echo -e "${RED}❌ Invalid or empty response${NC}"
    fi

    echo ""
}

###############################################################################
# Test Endpoints
###############################################################################

# Test 1: Home endpoint
test_endpoint "Home Page" "home" ""

# Test 2: Topics endpoint
test_endpoint "Topics List" "topics" ""

# Test 3: Chapters endpoint
test_endpoint "Chapters List" "chapters" ""

# Test 4: Top Funds endpoint
test_endpoint "Top Mutual Funds" "topfunds" ""

# Test 5: Search endpoint
test_endpoint "Search" "search" '"q":"loan"'

# Test 6: Search Cities
test_endpoint "Search Cities" "searchCity" '"q":"Mumbai"'

# Test 7: Get Blogs
test_endpoint "Blog Listing" "getBlogs" ""

# Test 8: Fetch Risk
test_endpoint "Risk Assessment" "fetchrisk" ""

# Test 9: Credit Card Listing
test_endpoint "Credit Card Listing" "getlisting" ""

###############################################################################
# Test Summary
###############################################################################

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   Test Complete!                                           ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${YELLOW}Tips:${NC}"
echo "1. If responses are empty, check database connection"
echo "2. Verify MySQL is running with correct credentials"
echo "3. Check server logs: docker-compose logs web"
echo "4. For detailed output, install jq: brew install jq"
echo ""
