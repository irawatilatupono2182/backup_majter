<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = '01234567-89ab-cdef-0123-456789abcdef'; // PT. Adam Jaya Utama
        
        $suppliers = [
            [
                'company_id' => $companyId,
                'supplier_code' => 'SUP001',
                'name' => 'PT. Elektronik Prima',
                'type' => 'Local',
                'email' => 'supplier@elektronikprima.com',
                'phone' => '021-77889900',
                'contact_person' => 'Pak Hendra',
                'address' => 'Jl. Elektronik No. 100, Jakarta Pusat, DKI Jakarta 10560',
                'is_active' => true,
            ],
            [
                'company_id' => $companyId,
                'supplier_code' => 'SUP002',
                'name' => 'CV. Komputer Nusantara',
                'type' => 'Local',
                'email' => 'sales@kompnusantara.com',
                'phone' => '021-44556677',
                'contact_person' => 'Bu Linda',
                'address' => 'Jl. Komputer No. 200, Jakarta Selatan, DKI Jakarta 12470',
                'is_active' => true,
            ],
            [
                'company_id' => $companyId,
                'supplier_code' => 'SUP003',
                'name' => 'PT. Office Supply Indo',
                'type' => 'Import',
                'email' => 'contact@officesupply.co.id',
                'phone' => '021-11223344',
                'contact_person' => 'Pak Tono',
                'address' => 'Jl. Office No. 300, Jakarta Timur, DKI Jakarta 13560',
                'is_active' => true,
            ],
            [
                'company_id' => $companyId,
                'supplier_code' => 'SUP004',
                'name' => 'UD. Alat Tulis Sejahtera',
                'type' => 'Local',
                'email' => 'alattulis@gmail.com',
                'phone' => '021-88990011',
                'contact_person' => 'Bu Ratna',
                'address' => 'Jl. Alat Tulis No. 400, Jakarta Barat, DKI Jakarta 11560',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}