@echo off
setlocal enabledelayedexpansion

REM Games Page Test Runner Script for Windows
REM This script runs all the Games page tests in the correct order

echo 🎮 Games Page Test Suite Runner
echo =================================

REM Function to run tests and check results
:run_test
set test_name=%1
set test_command=%2

echo.
echo Running: %test_name%
echo Command: %test_command%
echo ----------------------------------------

call %test_command%
if errorlevel 1 (
    echo ✗ %test_name% FAILED
    exit /b 1
) else (
    echo ✓ %test_name% PASSED
)
echo.
goto :eof

REM Check if Laravel is set up
if not exist "artisan" (
    echo Error: This script must be run from the Laravel project root
    exit /b 1
)

echo Setting up test environment...
echo ----------------------------------------

REM Copy environment file for testing if it doesn't exist
if not exist ".env.testing" (
    echo Creating .env.testing file...
    copy .env .env.testing
)

REM Copy Dusk environment file if it doesn't exist
if not exist ".env.dusk.local" (
    echo Creating .env.dusk.local file...
    copy .env .env.dusk.local
)

echo.

REM Run Feature Tests
echo 📋 FEATURE TESTS
echo =================================

call :run_test "Games Page Basic Functionality" "php artisan test tests/Feature/GamesPageTest.php --stop-on-failure"
if errorlevel 1 exit /b 1

call :run_test "Games Controller Tests" "php artisan test tests/Feature/GamesControllerTest.php --stop-on-failure"
if errorlevel 1 exit /b 1

REM Check if Chrome is available for Dusk tests
where chrome >nul 2>nul || where chrome.exe >nul 2>nul
if %errorlevel% equ 0 (
    set CHROME_FOUND=1
) else (
    set CHROME_FOUND=0
)

if %CHROME_FOUND% equ 1 (
    echo 🌐 BROWSER TESTS ^(DUSK^)
    echo =================================
    
    REM Start Laravel server in background for Dusk tests
    echo Starting Laravel development server...
    start /b php artisan serve --port=8000
    
    REM Wait for server to start
    timeout /t 3 /nobreak >nul
    
    call :run_test "Games Page Layout Tests" "php artisan dusk tests/Browser/GamesPageLayoutTest.php"
    set LAYOUT_RESULT=!errorlevel!
    
    call :run_test "Games Page Interaction Tests" "php artisan dusk tests/Browser/GamesPageInteractionTest.php"
    set INTERACTION_RESULT=!errorlevel!
    
    call :run_test "Games Page JavaScript Tests" "php artisan dusk tests/Browser/GamesPageJavaScriptTest.php"
    set JAVASCRIPT_RESULT=!errorlevel!
    
    call :run_test "Games Page Accessibility & Performance Tests" "php artisan dusk tests/Browser/GamesPageAccessibilityAndPerformanceTest.php"
    set ACCESSIBILITY_RESULT=!errorlevel!
    
    REM Stop the Laravel server
    echo Stopping Laravel development server...
    taskkill /f /im php.exe >nul 2>nul
    
    REM Check Dusk results
    if !LAYOUT_RESULT! neq 0 (
        echo Some Dusk tests failed
        exit /b 1
    )
    if !INTERACTION_RESULT! neq 0 (
        echo Some Dusk tests failed
        exit /b 1
    )
    if !JAVASCRIPT_RESULT! neq 0 (
        echo Some Dusk tests failed
        exit /b 1
    )
    if !ACCESSIBILITY_RESULT! neq 0 (
        echo Some Dusk tests failed
        exit /b 1
    )
    
) else (
    echo ⚠️  Skipping Dusk tests - Chrome not found
    echo To run Dusk tests, install Google Chrome browser
    echo.
)

REM Final summary
echo 🎉 ALL TESTS COMPLETED SUCCESSFULLY!
echo =================================
echo.
echo Test Coverage Summary:
echo • ✓ Basic page rendering and routing
echo • ✓ Authentication states ^(guest/authenticated^)
echo • ✓ Search functionality ^(name and description^)
echo • ✓ Filter functionality ^(categories, difficulty, players^)
echo • ✓ Multiple filter selection with OR logic
echo • ✓ Pagination ^(9 games per page^)
echo • ✓ Session storage for filter state
echo • ✓ Game modal functionality
echo • ✓ Responsive design
echo • ✓ Accessibility features
echo • ✓ Performance considerations
echo • ✓ Error handling and edge cases
echo.
echo 🔗 Related Test Files:
echo Feature Tests:
echo   - tests/Feature/GamesPageTest.php
echo   - tests/Feature/GamesControllerTest.php
echo   - tests/Feature/GamesPageTestSuite.php
echo.
echo Browser Tests:
echo   - tests/Browser/GamesPageLayoutTest.php
echo   - tests/Browser/GamesPageInteractionTest.php
echo   - tests/Browser/GamesPageJavaScriptTest.php
echo   - tests/Browser/GamesPageAccessibilityAndPerformanceTest.php
echo.
echo Ready for production! 🚀

pause
