@echo off
echo ========================================
echo Hazem Abdullah Portfolio Setup
echo ========================================
echo.

echo Step 1: Installing Composer dependencies...
call composer install
if %errorlevel% neq 0 (
    echo ERROR: Composer install failed!
    pause
    exit /b 1
)
echo.

echo Step 2: Installing NPM dependencies...
call npm install
if %errorlevel% neq 0 (
    echo ERROR: NPM install failed!
    pause
    exit /b 1
)
echo.

echo Step 3: Generating application key...
call php artisan key:generate
echo.

echo Step 4: Running database migrations and seeders...
call php artisan migrate:fresh --seed
if %errorlevel% neq 0 (
    echo ERROR: Database migration failed!
    echo Please ensure MySQL is running and database 'hazem' exists.
    pause
    exit /b 1
)
echo.

echo Step 5: Creating storage link...
call php artisan storage:link
echo.

echo Step 6: Building frontend assets...
call npm run build
if %errorlevel% neq 0 (
    echo ERROR: Build failed!
    pause
    exit /b 1
)
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Your website is ready at: http://localhost/hazem/
echo Admin panel: http://localhost/hazem/admin/login
echo.
echo Default admin credentials:
echo Email: admin@hazem.com
echo Password: admin123
echo.
echo IMPORTANT: Change the admin password after first login!
echo.
pause
