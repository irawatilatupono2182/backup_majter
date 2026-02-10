<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Company;
use App\Models\UserCompanyRole;
use Illuminate\Support\Facades\Hash;

echo "=== CREATE TEST USER FOR LOGIN ===\n\n";

// 1. Cek atau buat company dulu
$company = Company::firstOrCreate(
    ['company_id' => '01234567-89ab-cdef-0123-456789abcdef'],
    [
        'code' => 'TESTCOMP',
        'name' => 'PT Test Company',
        'address' => 'Jakarta',
        'phone' => '021-1234567',
        'email' => 'test@company.com',
    ]
);

echo "✅ Company: {$company->name} (ID: {$company->company_id})\n\n";

// 2. Cek atau buat user
$user = User::firstWhere('email', 'admin@test.com');

if ($user) {
    echo "User 'admin@test.com' already exists. Updating...\n";
    $user->update([
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);
} else {
    echo "Creating new user 'admin@test.com'...\n";
    $user = User::create([
        'username' => 'admin',
        'name' => 'Admin Test',
        'email' => 'admin@test.com',
        'password' => Hash::make('password'),
        'phone' => '081234567890',
        'is_active' => true,
    ]);
}

echo "✅ User: {$user->email} (ID: {$user->id})\n";
echo "   Active: " . ($user->is_active ? 'Yes ✅' : 'No ❌') . "\n\n";

// 3. Link user ke company jika belum
$hasCompany = UserCompanyRole::where('user_id', $user->id)
    ->where('company_id', $company->company_id)
    ->exists();

if (!$hasCompany) {
    echo "Linking user to company...\n";
    UserCompanyRole::create([
        'user_id' => $user->id,
        'company_id' => $company->company_id,
        'role' => 'admin',
        'is_default' => true,
    ]);
    echo "✅ User linked to company\n\n";
} else {
    echo "✅ User already linked to company\n\n";
}

// 4. Test password
$passwordWorks = Hash::check('password', $user->password);
echo "Password test: " . ($passwordWorks ? '✅ WORKS' : '❌ FAILED') . "\n\n";

// 5. Test canAccessPanel
try {
    $panel = \Filament\Facades\Filament::getDefaultPanel();
    $canAccess = $user->canAccessPanel($panel);
    echo "Can access Filament panel: " . ($canAccess ? '✅ YES' : '❌ NO') . "\n\n";
} catch (\Exception $e) {
    echo "Can access Filament panel: ⚠️ " . $e->getMessage() . "\n\n";
}

echo "===============================\n";
echo "✅ SETUP COMPLETE!\n\n";
echo "You can now login with:\n";
echo "Email: admin@test.com\n";
echo "Password: password\n";
echo "===============================\n";
