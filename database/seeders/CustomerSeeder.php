<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = '01234567-89ab-cdef-0123-456789abcdef'; // PT. Adam Jaya Utama
        
        $customers = [
            [
                'company_id' => $companyId,
                'customer_code' => 'CUST001',
                'name' => 'PT. Mitra Sejahtera',
                'contact_person' => 'Budi Santoso',
                'email' => 'contact@mitrasejahtera.com',
                'phone' => '021-55667788',
                'address_ship_to' => 'Jl. Mitra No. 123, Jakarta Selatan, DKI Jakarta 12560',
                'address_bill_to' => 'Jl. Mitra No. 123, Jakarta Selatan, DKI Jakarta 12560',
                'npwp' => '01.111.222.3-444.000',
                'billing_schedule' => 'Setiap tanggal 5',
                'is_ppn' => true,
                'is_active' => true,
            ],
            [
                'company_id' => $companyId,
                'customer_code' => 'CUST002',
                'name' => 'CV. Berkah Jaya',
                'contact_person' => 'Siti Aminah',
                'email' => 'admin@berkahjaya.com',
                'phone' => '021-99887766',
                'address_ship_to' => 'Jl. Berkah No. 456, Jakarta Timur, DKI Jakarta 13460',
                'address_bill_to' => 'Jl. Berkah No. 456, Jakarta Timur, DKI Jakarta 13460',
                'npwp' => '01.555.666.7-888.000',
                'billing_schedule' => 'Minggu ke-2 setiap bulan',
                'is_ppn' => true,
                'is_active' => true,
            ],
            [
                'company_id' => $companyId,
                'customer_code' => 'CUST003',
                'name' => 'PT. Global Mandiri',
                'contact_person' => 'Ahmad Fauzi',
                'email' => 'sales@globalmandiri.com',
                'phone' => '021-33445566',
                'address_ship_to' => 'Jl. Global No. 789, Jakarta Barat, DKI Jakarta 11470',
                'address_bill_to' => 'Jl. Global No. 789, Jakarta Barat, DKI Jakarta 11470',
                'npwp' => '01.999.888.7-666.000',
                'billing_schedule' => 'Setiap akhir bulan',
                'is_ppn' => true,
                'is_active' => true,
            ],
            [
                'company_id' => $companyId,
                'customer_code' => 'CUST004',
                'name' => 'Toko Sinar Harapan',
                'contact_person' => 'Ibu Dewi',
                'email' => 'sinarharapan@gmail.com',
                'phone' => '021-22334455',
                'address_ship_to' => 'Jl. Harapan No. 321, Jakarta Utara, DKI Jakarta 14350',
                'address_bill_to' => 'Jl. Harapan No. 321, Jakarta Utara, DKI Jakarta 14350',
                'npwp' => null,
                'billing_schedule' => 'Setiap tanggal 15',
                'is_ppn' => false,
                'is_active' => true,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}