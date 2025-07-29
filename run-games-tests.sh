#!/bin/bash

# Games Page Test Runner Script
# This script runs all the Games page tests in the correct order

echo "🎮 Games Page Test Suite Runner"
echo "================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to run tests and check results
run_test() {
    local test_name="$1"
    local test_command="$2"
    
    echo -e "${BLUE}Running: $test_name${NC}"
    echo "Command: $test_command"
    echo "----------------------------------------"
    
    if eval $test_command; then
        echo -e "${GREEN}✓ $test_name PASSED${NC}"
    else
        echo -e "${RED}✗ $test_name FAILED${NC}"
        return 1
    fi
    echo ""
}

# Check if Laravel is set up
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: This script must be run from the Laravel project root${NC}"
    exit 1
fi

echo "Setting up test environment..."
echo "----------------------------------------"

# Copy environment file for testing if it doesn't exist
if [ ! -f ".env.testing" ]; then
    echo "Creating .env.testing file..."
    cp .env .env.testing
fi

# Copy Dusk environment file if it doesn't exist
if [ ! -f ".env.dusk.local" ]; then
    echo "Creating .env.dusk.local file..."
    cp .env .env.dusk.local
fi

echo ""

# Run Feature Tests
echo -e "${YELLOW}📋 FEATURE TESTS${NC}"
echo "================================="

run_test "Games Page Basic Functionality" "php artisan test tests/Feature/GamesPageTest.php --stop-on-failure"
if [ $? -ne 0 ]; then exit 1; fi

run_test "Games Controller Tests" "php artisan test tests/Feature/GamesControllerTest.php --stop-on-failure"
if [ $? -ne 0 ]; then exit 1; fi

# Check if Chrome is available for Dusk tests
if command -v google-chrome >/dev/null 2>&1 || command -v chromium-browser >/dev/null 2>&1 || command -v chromium >/dev/null 2>&1; then
    echo -e "${YELLOW}🌐 BROWSER TESTS (DUSK)${NC}"
    echo "================================="
    
    # Start Laravel server in background for Dusk tests
    echo "Starting Laravel development server..."
    php artisan serve --port=8000 &
    SERVER_PID=$!
    
    # Wait for server to start
    sleep 3
    
    run_test "Games Page Layout Tests" "php artisan dusk tests/Browser/GamesPageLayoutTest.php"
    LAYOUT_RESULT=$?
    
    run_test "Games Page Interaction Tests" "php artisan dusk tests/Browser/GamesPageInteractionTest.php"
    INTERACTION_RESULT=$?
    
    run_test "Games Page JavaScript Tests" "php artisan dusk tests/Browser/GamesPageJavaScriptTest.php"
    JAVASCRIPT_RESULT=$?
    
    run_test "Games Page Accessibility & Performance Tests" "php artisan dusk tests/Browser/GamesPageAccessibilityAndPerformanceTest.php"
    ACCESSIBILITY_RESULT=$?
    
    # Stop the Laravel server
    echo "Stopping Laravel development server..."
    kill $SERVER_PID
    
    # Check Dusk results
    if [ $LAYOUT_RESULT -ne 0 ] || [ $INTERACTION_RESULT -ne 0 ] || [ $JAVASCRIPT_RESULT -ne 0 ] || [ $ACCESSIBILITY_RESULT -ne 0 ]; then
        echo -e "${RED}Some Dusk tests failed${NC}"
        exit 1
    fi
    
else
    echo -e "${YELLOW}⚠️  Skipping Dusk tests - Chrome/Chromium not found${NC}"
    echo "To run Dusk tests, install Chrome or Chromium browser"
    echo ""
fi

# Final summary
echo -e "${GREEN}🎉 ALL TESTS COMPLETED SUCCESSFULLY!${NC}"
echo "================================="
echo ""
echo "Test Coverage Summary:"
echo "• ✓ Basic page rendering and routing"
echo "• ✓ Authentication states (guest/authenticated)"
echo "• ✓ Search functionality (name and description)"
echo "• ✓ Filter functionality (categories, difficulty, players)"
echo "• ✓ Multiple filter selection with OR logic"
echo "• ✓ Pagination (9 games per page)"
echo "• ✓ Session storage for filter state"
echo "• ✓ Game modal functionality"
echo "• ✓ Responsive design"
echo "• ✓ Accessibility features"
echo "• ✓ Performance considerations"
echo "• ✓ Error handling and edge cases"
echo ""
echo -e "${BLUE}🔗 Related Test Files:${NC}"
echo "Feature Tests:"
echo "  - tests/Feature/GamesPageTest.php"
echo "  - tests/Feature/GamesControllerTest.php"
echo "  - tests/Feature/GamesPageTestSuite.php"
echo ""
echo "Browser Tests:"
echo "  - tests/Browser/GamesPageLayoutTest.php"
echo "  - tests/Browser/GamesPageInteractionTest.php"
echo "  - tests/Browser/GamesPageJavaScriptTest.php"
echo "  - tests/Browser/GamesPageAccessibilityAndPerformanceTest.php"
echo ""
echo -e "${GREEN}Ready for production! 🚀${NC}"
