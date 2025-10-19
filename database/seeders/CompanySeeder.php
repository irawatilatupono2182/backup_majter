<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo companies
        $companies = [
            [
                'company_id' => '01234567-89ab-cdef-0123-456789abcdef',
                'name' => 'PT. Adam Jaya Utama',
                'code' => 'AJU',
                'address' => 'Jl. Raya Jakarta No. 123, Jakarta Pusat',
                'phone' => '021-12345678',
                'email' => 'info@adamjaya.com',
                'npwp' => '01.234.567.8-901.000',
            ],
            [
                'company_id' => '12345678-9abc-def0-1234-56789abcdef0',
                'name' => 'CV. Sukses Mandiri',
                'code' => 'SM',
                'address' => 'Jl. Merdeka No. 456, Surabaya',
                'phone' => '031-98765432',
                'email' => 'contact@suksesmandiri.com',
                'npwp' => '02.345.678.9-012.000',
            ],
            [
                'company_id' => '23456789-abcd-ef01-2345-6789abcdef01',
                'name' => 'PT. Teknologi Maju',
                'code' => 'TM',
                'address' => 'Jl. Teknologi No. 789, Bandung',
                'phone' => '022-11223344',
                'email' => 'admin@teknologimaju.com',
                'npwp' => '03.456.789.0-123.000',
            ],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }
    }
}
