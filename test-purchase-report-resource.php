<?php

// Quick test to check if PurchaseReportResource can be loaded

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing PurchaseReportResource...\n\n";

try {
    $resourceClass = \App\Filament\Resources\PurchaseReportResource::class;
    echo "✅ Class exists: {$resourceClass}\n";
    
    // Check if it's a valid Filament resource
    if (is_subclass_of($resourceClass, \Filament\Resources\Resource::class)) {
        echo "✅ Is a valid Filament Resource\n";
    } else {
        echo "❌ Not a valid Filament Resource\n";
    }
    
    // Check navigation properties
    echo "\nNavigation Properties:\n";
    echo "- Icon: " . $resourceClass::getNavigationIcon() . "\n";
    echo "- Label: " . $resourceClass::getNavigationLabel() . "\n";
    echo "- Group: " . $resourceClass::getNavigationGroup() . "\n";
    echo "- Sort: " . $resourceClass::getNavigationSort() . "\n";
    
    // Check if navigation is registered
    $reflection = new \ReflectionClass($resourceClass);
    if ($reflection->hasMethod('shouldRegisterNavigation')) {
        $shouldRegister = $resourceClass::shouldRegisterNavigation();
        echo "- Should Register: " . ($shouldRegister ? 'Yes ✅' : 'No ❌') . "\n";
    } else {
        echo "- Should Register: Yes ✅ (default)\n";
    }
    
    // Check pages
    echo "\nPages:\n";
    $pages = $resourceClass::getPages();
    foreach ($pages as $name => $page) {
        echo "- {$name}: " . get_class($page) . "\n";
    }
    
    // Try to instantiate the list page
    echo "\nTrying to load ListPurchaseReports page...\n";
    $listPageClass = \App\Filament\Resources\PurchaseReportResource\Pages\ListPurchaseReports::class;
    if (class_exists($listPageClass)) {
        echo "✅ ListPurchaseReports class exists\n";
    } else {
        echo "❌ ListPurchaseReports class not found\n";
    }
    
    echo "\n✅ All checks passed! Resource should be visible.\n";
    echo "\nIf still not visible, try:\n";
    echo "1. php artisan optimize:clear\n";
    echo "2. php artisan filament:cache-components\n";
    echo "3. Restart the server\n";
    
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
