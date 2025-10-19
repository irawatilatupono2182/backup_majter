<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = '01234567-89ab-cdef-0123-456789abcdef'; // PT. Adam Jaya Utama
        
        $products = [
            // Elektronik
            [
                'company_id' => $companyId,
                'product_code' => 'DELL-INS-15-3000',
                'name' => 'Laptop Dell Inspiron 15 3000',
                'description' => 'Laptop Dell Inspiron 15 3000 Series dengan Intel Core i3, RAM 4GB, HDD 1TB',
                'category' => 'Elektronik',
                'unit' => 'unit',
                'base_price' => 10000000,
                'default_discount_percent' => 5,
                'min_stock_alert' => 5,
                'product_type' => 'STOCK',
                'is_active' => true,
            ],
            [
                'company_id' => $companyId,
                'product_code' => 'LOG-M705',
                'name' => 'Mouse Wireless Logitech M705',
                'description' => 'Mouse wireless Logitech M705 dengan teknologi unifying receiver',
                'category' => 'Aksesori Komputer',
                'unit' => 'unit',
                'base_price' => 450000,
                'default_discount_percent' => 10,
                'min_stock_alert' => 10,
                'product_type' => 'STOCK',
                'is_active' => true,
            ],
            [
                'company_id' => $companyId,
                'product_code' => 'KB-MECH-RGB',
                'name' => 'Keyboard Mechanical RGB',
                'description' => 'Keyboard mechanical dengan lampu RGB backlight',
                'category' => 'Aksesori Komputer',
                'unit' => 'unit',
                'base_price' => 950000,
                'default_discount_percent' => 5,
                'min_stock_alert' => 8,
                'product_type' => 'STOCK',
                'is_active' => true,
            ],
            
            // Alat Tulis
            [
                'company_id' => $companyId,
                'product_code' => 'KRT-A4-80',
                'name' => 'Kertas A4 80gsm',
                'description' => 'Kertas A4 80gsm putih 500 lembar per rim',
                'category' => 'Alat Tulis',
                'unit' => 'rim',
                'base_price' => 55000,
                'default_discount_percent' => 0,
                'min_stock_alert' => 20,
                'product_type' => 'STOCK',
                'is_active' => true,
            ],
            [
                'company_id' => $companyId,
                'product_code' => 'PEN-PILOT-G2',
                'name' => 'Pulpen Pilot G2 0.7mm',
                'description' => 'Pulpen gel Pilot G2 dengan tip 0.7mm, warna hitam',
                'category' => 'Alat Tulis',
                'unit' => 'pcs',
                'base_price' => 7500,
                'default_discount_percent' => 0,
                'min_stock_alert' => 50,
                'product_type' => 'STOCK',
                'is_active' => true,
            ],
            [
                'company_id' => $companyId,
                'product_code' => 'SPD-WB',
                'name' => 'Spidol Whiteboard',
                'description' => 'Spidol untuk papan tulis putih, berbagai warna',
                'category' => 'Alat Tulis',
                'unit' => 'pcs',
                'base_price' => 12000,
                'default_discount_percent' => 0,
                'min_stock_alert' => 30,
                'product_type' => 'STOCK',
                'is_active' => true,
            ],
            
            // Jasa (Catalog only)
            [
                'company_id' => $companyId,
                'product_code' => 'SVC-INSTALL-SW',
                'name' => 'Jasa Instalasi Software',
                'description' => 'Jasa instalasi dan konfigurasi software komputer',
                'category' => 'Jasa',
                'unit' => 'jam',
                'base_price' => 100000,
                'default_discount_percent' => 0,
                'min_stock_alert' => 0,
                'product_type' => 'CATALOG',
                'is_active' => true,
            ],
            [
                'company_id' => $companyId,
                'product_code' => 'SVC-MAINT-PC',
                'name' => 'Jasa Maintenance Komputer',
                'description' => 'Jasa maintenance dan perbaikan komputer/laptop',
                'category' => 'Jasa',
                'unit' => 'kali',
                'base_price' => 250000,
                'default_discount_percent' => 0,
                'min_stock_alert' => 0,
                'product_type' => 'CATALOG',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}