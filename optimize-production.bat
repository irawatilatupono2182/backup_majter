@echo off
echo ================================================
echo   OPTIMIZING LARAVEL FOR PRODUCTION
echo ================================================
echo.

echo [1/8] Clearing all caches...
php artisan optimize:clear

echo.
echo [2/8] Caching configuration...
php artisan config:cache

echo.
echo [3/8] Caching routes...
php artisan route:cache

echo.
echo [4/8] Caching views...
php artisan view:cache

echo.
echo [5/8] Caching events...
php artisan event:cache

echo.
echo [6/8] Optimizing autoloader...
composer dump-autoload --optimize --no-dev --classmap-authoritative

echo.
echo [7/8] Caching icons (Filament)...
php artisan icons:cache

echo.
echo [8/8] Caching Filament components...
php artisan filament:cache-components

echo.
echo ================================================
echo   OPTIMIZATION COMPLETE!
echo ================================================
echo.
echo Performance tips:
echo - Enable OPcache in php.ini
echo - Use file or Redis for cache/session
echo - Keep debug mode OFF in production
echo - Run this script after every deployment
echo.
pause
