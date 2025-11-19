<?php

namespace Tests\Unit\Helpers;

use App\Models\Company;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Support\Facades\Hash;

/**
 * Test Data Factory Helper
 *
 * Provides convenient methods to create test data with proper relationships
 */
class TestDataFactory
{
    /**
     * Create a test company
     */
    public static function createCompany(array $overrides = []): Company
    {
        return Company::factory()->create(array_merge([
            'company_name' => 'Test Company ' . uniqid(),
            'is_active' => true,
        ], $overrides));
    }

    /**
     * Create a test user with role
     */
    public static function createUser(Company $company, string $role = 'user', array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'name' => 'Test User ' . uniqid(),
            'email' => 'test' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ], $overrides));

        $user->assignRole($role);

        return $user;
    }

    /**
     * Create a test product
     */
    public static function createProduct(Company $company, array $overrides = []): Product
    {
        return Product::factory()->create(array_merge([
            'company_id' => $company->company_id,
            'product_type' => 'STOCK',
            'is_active' => true,
        ], $overrides));
    }

    /**
     * Create a test customer
     */
    public static function createCustomer(Company $company, array $overrides = []): Customer
    {
        return Customer::factory()->create(array_merge([
            'company_id' => $company->company_id,
            'is_active' => true,
        ], $overrides));
    }

    /**
     * Create a test supplier
     */
    public static function createSupplier(Company $company, array $overrides = []): Supplier
    {
        return Supplier::factory()->create(array_merge([
            'company_id' => $company->company_id,
            'is_active' => true,
        ], $overrides));
    }

    /**
     * Create multiple products
     */
    public static function createProducts(Company $company, int $count = 5): \Illuminate\Support\Collection
    {
        return collect(range(1, $count))->map(function ($i) use ($company) {
            return self::createProduct($company, [
                'product_code' => 'PROD-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => "Test Product {$i}",
            ]);
        });
    }

    /**
     * Create multiple customers
     */
    public static function createCustomers(Company $company, int $count = 5): \Illuminate\Support\Collection
    {
        return collect(range(1, $count))->map(function () use ($company) {
            return self::createCustomer($company);
        });
    }
}
