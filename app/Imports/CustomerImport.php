<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Company;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    public function model(array $row): ?Customer
    {
        return new Customer([
            'company_id' => Auth::user()->selected_company_id,
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'] ?? null,
            'address' => $row['address'] ?? null,
            'city' => $row['city'] ?? null,
            'postal_code' => $row['postal_code'] ?? null,
            'tax_number' => $row['tax_number'] ?? null,
            'credit_limit' => $row['credit_limit'] ?? 0,
            'payment_terms' => $row['payment_terms'] ?? 30,
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
                Rule::unique('customers')->where(function ($query) {
                    return $query->where('company_id', Auth::user()->selected_company_id)
                                 ->whereNull('deleted_at');
                })
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers')->where(function ($query) {
                    return $query->where('company_id', Auth::user()->selected_company_id)
                                 ->whereNull('deleted_at');
                })
            ],
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('customers')->where(function ($query) {
                    return $query->where('company_id', Auth::user()->selected_company_id)
                                 ->whereNull('deleted_at');
                })
            ],
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|integer|min:1|max:365',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'name.required' => 'Nama pelanggan wajib diisi',
            'name.unique' => 'Nama pelanggan sudah digunakan',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'tax_number.unique' => 'NPWP sudah digunakan',
            'credit_limit.numeric' => 'Limit kredit harus berupa angka',
            'credit_limit.min' => 'Limit kredit tidak boleh negatif',
            'payment_terms.integer' => 'Termin pembayaran harus berupa angka',
            'payment_terms.min' => 'Termin pembayaran minimal 1 hari',
            'payment_terms.max' => 'Termin pembayaran maksimal 365 hari',
        ];
    }
}