<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Company Management
            'view_companies',
            'create_companies',
            'edit_companies',
            'delete_companies',

            // User Management
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            'manage_user_roles',

            // Customer Management
            'view_customers',
            'create_customers',
            'edit_customers',
            'delete_customers',

            // Supplier Management
            'view_suppliers',
            'create_suppliers',
            'edit_suppliers',
            'delete_suppliers',

            // Product Management
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',

            // Price Quotation
            'view_price_quotations',
            'create_price_quotations',
            'edit_price_quotations',
            'delete_price_quotations',
            'approve_price_quotations',

            // Purchase Order
            'view_purchase_orders',
            'create_purchase_orders',
            'edit_purchase_orders',
            'delete_purchase_orders',
            'approve_purchase_orders',
            'receive_purchase_orders',

            // Delivery Note
            'view_delivery_notes',
            'create_delivery_notes',
            'edit_delivery_notes',
            'delete_delivery_notes',
            'deliver_items',

            // Invoice
            'view_invoices',
            'create_invoices',
            'edit_invoices',
            'delete_invoices',

            // Payment
            'view_payments',
            'create_payments',
            'edit_payments',
            'delete_payments',

            // Stock Management
            'view_stocks',
            'create_stocks',
            'edit_stocks',
            'delete_stocks',
            'adjust_stocks',

            // Stock Movement
            'view_stock_movements',
            'create_stock_movements',
            'edit_stock_movements',
            'delete_stock_movements',

            // Reports
            'view_sales_reports',
            'export_sales_reports',
            'view_inventory_reports',
            'export_inventory_reports',
            'view_notifications',

            // PDF Downloads
            'download_pdfs',
            'preview_pdfs',

            // Settings
            'manage_settings',
            'backup_system',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions([
            'view_companies', 'edit_companies',
            'view_users', 'create_users', 'edit_users', 'manage_user_roles',
            'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
            'view_suppliers', 'create_suppliers', 'edit_suppliers', 'delete_suppliers',
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'view_price_quotations', 'create_price_quotations', 'edit_price_quotations', 'approve_price_quotations',
            'view_purchase_orders', 'create_purchase_orders', 'edit_purchase_orders', 'approve_purchase_orders', 'receive_purchase_orders',
            'view_delivery_notes', 'create_delivery_notes', 'edit_delivery_notes', 'deliver_items',
            'view_invoices', 'create_invoices', 'edit_invoices',
            'view_payments', 'create_payments', 'edit_payments',
            'view_stocks', 'create_stocks', 'edit_stocks', 'adjust_stocks',
            'view_stock_movements', 'create_stock_movements',
            'view_sales_reports', 'export_sales_reports',
            'view_inventory_reports', 'export_inventory_reports',
            'view_notifications',
            'download_pdfs', 'preview_pdfs',
        ]);

        $financeRole = Role::firstOrCreate(['name' => 'finance', 'guard_name' => 'web']);
        $financeRole->syncPermissions([
            'view_customers', 'create_customers', 'edit_customers',
            'view_suppliers', 'create_suppliers', 'edit_suppliers',
            'view_price_quotations', 'create_price_quotations', 'edit_price_quotations',
            'view_purchase_orders', 'create_purchase_orders', 'edit_purchase_orders',
            'view_delivery_notes', 'create_delivery_notes', 'edit_delivery_notes',
            'view_invoices', 'create_invoices', 'edit_invoices',
            'view_payments', 'create_payments', 'edit_payments',
            'view_sales_reports', 'export_sales_reports',
            'download_pdfs', 'preview_pdfs',
        ]);

        $warehouseRole = Role::firstOrCreate(['name' => 'warehouse', 'guard_name' => 'web']);
        $warehouseRole->syncPermissions([
            'view_products',
            'view_purchase_orders', 'receive_purchase_orders',
            'view_delivery_notes', 'create_delivery_notes', 'edit_delivery_notes', 'deliver_items',
            'view_stocks', 'create_stocks', 'edit_stocks', 'adjust_stocks',
            'view_stock_movements', 'create_stock_movements',
            'view_inventory_reports', 'export_inventory_reports',
            'view_notifications',
            'download_pdfs', 'preview_pdfs',
        ]);

        $viewerRole = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewerRole->syncPermissions([
            'view_customers',
            'view_suppliers',
            'view_products',
            'view_price_quotations',
            'view_purchase_orders',
            'view_delivery_notes',
            'view_invoices',
            'view_payments',
            'view_stocks',
            'view_stock_movements',
            'view_sales_reports',
            'view_inventory_reports',
            'view_notifications',
            'preview_pdfs',
        ]);
    }
}