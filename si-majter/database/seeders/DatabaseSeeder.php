<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed in specific order to maintain referential integrity
        $this->call([
            PermissionSeeder::class,    // First: Create roles and permissions
            CompanySeeder::class,       // Second: Create companies
            UserSeeder::class,          // Third: Create users and assign roles
            CustomerSeeder::class,      // Fourth: Create customers
            SupplierSeeder::class,      // Fifth: Create suppliers
            ProductSeeder::class,       // Sixth: Create products
            // TransactionSeeder::class, // Optional: Create sample transactions
        ]);

        $this->command->info('Database seeding completed successfully!');
        $this->command->info('Default login credentials:');
        $this->command->info('Admin: admin@adamjaya.com / password');
        $this->command->info('Finance: finance@adamjaya.com / password');
        $this->command->info('Warehouse: warehouse@adamjaya.com / password');
        $this->command->info('Viewer: viewer@adamjaya.com / password');
    }
}
