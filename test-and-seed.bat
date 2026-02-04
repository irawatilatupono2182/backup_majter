@echo off
echo ========================================
echo TESTING & SEEDING DATABASE
echo ========================================
echo.

echo [1/5] Running Fresh Migration...
php artisan migrate:fresh --force
echo.

echo [2/5] Running Comprehensive Seeder...
php artisan db:seed --class=ComprehensiveTestSeeder
echo.

echo [3/5] Sending Manual Notifications...
php artisan notifications:send
echo.

echo [4/5] Clearing All Cache...
php artisan optimize:clear
echo.

echo [5/5] Checking Data Count...
php artisan tinker --execute="echo 'Customers: ' . \App\Models\Customer::count() . PHP_EOL; echo 'Products: ' . \App\Models\Product::count() . PHP_EOL; echo 'Stocks: ' . \App\Models\Stock::count() . PHP_EOL; echo 'POs: ' . \App\Models\PurchaseOrder::count() . PHP_EOL; echo 'Invoices: ' . \App\Models\Invoice::count() . PHP_EOL; echo 'Notifications: ' . \Illuminate\Notifications\DatabaseNotification::count() . PHP_EOL;"
echo.

echo ========================================
echo DONE! Check your admin panel now
echo ========================================
pause
