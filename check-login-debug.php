<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

echo "============= LOGIN DEBUG =============\n\n";

// 1. Check total users
$totalUsers = User::count();
echo "Total users in database: {$totalUsers}\n";

if ($totalUsers === 0) {
    echo "❌ NO USERS FOUND! You need to run seeder first.\n";
    echo "Run: art db:seed\n";
    exit(1);
}

// 2. Check active users
$activeUsers = User::where('is_active', true)->count();
echo "Active users: {$activeUsers}\n";

// 3. List all users with details
echo "\n--- User Details ---\n";
$users = User::with('companies')->get();
foreach ($users as $user) {
    echo "\nEmail: {$user->email}\n";
    echo "Username: {$user->username}\n";
    echo "Is Active: " . ($user->is_active ? 'Yes ✅' : 'No ❌') . "\n";
    echo "Has Companies: " . ($user->companies->count() > 0 ? 'Yes (' . $user->companies->count() . ') ✅' : 'No ❌') . "\n";
    
    if ($user->companies->count() > 0) {
        foreach ($user->companies as $company) {
            echo "  - Company: {$company->name} ({$company->code})\n";
        }
    }
    
    // Test password
    $testPassword = 'password';
    $passwordWorks = Hash::check($testPassword, $user->password);
    echo "Password 'password' works: " . ($passwordWorks ? 'Yes ✅' : 'No ❌') . "\n";
    
    // Can access panel?
    try {
        $panel = \Filament\Facades\Filament::getCurrentPanel();
        if (method_exists($user, 'canAccessPanel')) {
            $canAccess = $user->canAccessPanel($panel);
            echo "Can access Filament panel: " . ($canAccess ? 'Yes ✅' : 'No ❌') . "\n";
        }
    } catch (\Exception $e) {
        echo "Can access Filament panel: Error - " . $e->getMessage() . "\n";
    }
}

// 4. Check companies
echo "\n\n--- Companies ---\n";
$totalCompanies = Company::count();
echo "Total companies: {$totalCompanies}\n";

if ($totalCompanies === 0) {
    echo "❌ NO COMPANIES FOUND! Users need at least one company.\n";
}

$companies = Company::limit(5)->get();
foreach ($companies as $company) {
    echo "  - {$company->name} ({$company->code}) - ID: {$company->company_id}\n";
}

echo "\n\n============= SUMMARY =============\n";

if ($totalUsers === 0) {
    echo "❌ ISSUE: No users in database\n";
    echo "   SOLUTION: Run 'art db:seed' or 'art db:seed --class=ComprehensiveTestSeederFixed'\n\n";
} elseif ($activeUsers === 0) {
    echo "❌ ISSUE: No active users\n";
    echo "   SOLUTION: Set is_active=1 for at least onе user\n\n";
} elseif ($totalCompanies === 0) {
    echo "❌ ISSUE: No companies in database\n";
    echo "   SOLUTION: Run company seeder or create a company\n\n";
} else {
    $usersWithCompanies = User::whereHas('companies')->count();
    if ($usersWithCompanies === 0) {
        echo "❌ ISSUE: Users exist but have no companies linked\n";
        echo "   SOLUTION: Link users to companies via user_company_roles table\n\n";
    } else {
        echo "✅ Database looks good!\n";
        echo "   Try logging in with:\n";
        $firstUser = User::where('is_active', true)->first();
        if ($firstUser) {
            echo "   Email: {$firstUser->email}\n";
            echo "   Password: password\n\n";
        }
    }
}

echo "=========================================\n";
