@echo off
echo ========================================
echo CHECKING NOTIFICATION DATA
echo ========================================
echo.

echo [1/5] Low Stock Items:
php artisan tinker --execute="echo \App\Models\Stock::whereColumn('available_quantity', '<', 'minimum_stock')->count();"
echo.

echo [2/5] Expired Stock Items:
php artisan tinker --execute="echo \App\Models\Stock::whereNotNull('expiry_date')->where('expiry_date', '<', now())->count();"
echo.

echo [3/5] Overdue Invoices:
php artisan tinker --execute="echo \App\Models\Invoice::whereIn('status', ['Unpaid', 'Partial'])->where('due_date', '<', now())->count();"
echo.

echo [4/5] Overdue Purchase Orders:
php artisan tinker --execute="echo \App\Models\PurchaseOrder::whereIn('payment_status', ['unpaid', 'partial'])->whereNotNull('due_date')->where('due_date', '<', now())->count();"
echo.

echo [5/5] Sending Notifications...
php artisan notifications:send
echo.

echo [6/6] Total Notifications in Database:
php artisan tinker --execute="echo \Illuminate\Notifications\DatabaseNotification::count();"
echo.

echo ========================================
echo DONE! Refresh admin panel and check bell icon
echo ========================================
pause
