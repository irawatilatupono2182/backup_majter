@echo off
echo ================================================
echo   CLEARING ALL LARAVEL CACHES
echo ================================================
echo.

echo [1/6] Clearing application cache...
php artisan cache:clear

echo.
echo [2/6] Clearing route cache...
php artisan route:clear

echo.
echo [3/6] Clearing config cache...
php artisan config:clear

echo.
echo [4/6] Clearing view cache...
php artisan view:clear

echo.
echo [5/6] Clearing compiled classes...
php artisan clear-compiled

echo.
echo [6/6] Clearing icon cache...
php artisan icons:clear

echo.
echo ================================================
echo   ALL CACHES CLEARED!
echo ================================================
echo.
echo Use this during development when you make
echo changes to config, routes, or views.
echo.
pause
