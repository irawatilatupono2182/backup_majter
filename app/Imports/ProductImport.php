<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Company;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    public function model(array $row): ?Product
    {
        return new Product([
            'company_id' => Auth::user()->selected_company_id,
            'name' => $row['name'],
            'code' => $row['code'] ?? null,
            'description' => $row['description'] ?? null,
            'category' => $row['category'] ?? 'Umum',
            'unit' => $row['unit'] ?? 'pcs',
            'purchase_price' => $row['purchase_price'] ?? 0,
            'sale_price' => $row['sale_price'] ?? 0,
            'min_stock' => $row['min_stock'] ?? 0,
            'max_stock' => $row['max_stock'] ?? null,
            'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->where(function ($query) {
                    return $query->where('company_id', Auth::user()->selected_company_id)
                                 ->whereNull('deleted_at');
                })
            ],
            'code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products')->where(function ($query) {
                    return $query->where('company_id', Auth::user()->selected_company_id)
                                 ->whereNull('deleted_at');
                })
            ],
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi',
            'name.unique' => 'Nama produk sudah digunakan',
            'code.unique' => 'Kode produk sudah digunakan',
            'purchase_price.numeric' => 'Harga beli harus berupa angka',
            'purchase_price.min' => 'Harga beli tidak boleh negatif',
            'sale_price.numeric' => 'Harga jual harus berupa angka',
            'sale_price.min' => 'Harga jual tidak boleh negatif',
            'min_stock.integer' => 'Stok minimum harus berupa angka',
            'min_stock.min' => 'Stok minimum tidak boleh negatif',
            'max_stock.integer' => 'Stok maksimum harus berupa angka',
            'max_stock.min' => 'Stok maksimum tidak boleh negatif',
        ];
    }
}