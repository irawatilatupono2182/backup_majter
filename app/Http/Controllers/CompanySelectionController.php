<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanySelectionController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();
        $companies = $user->companies()->get();

        return view('company.select', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,company_id'
        ]);

        $user = auth()->user();
        $companyId = $request->company_id;

        // Verify user has access to this company
        if (!$user->companies()->where('company_id', $companyId)->exists()) {
            abort(403, 'You do not have access to this company.');
        }

        session(['selected_company_id' => $companyId]);

        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }
}