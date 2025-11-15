<?php

use App\Http\Controllers\CompanySelectionController;
use App\Http\Controllers\PDFController;
use Illuminate\Support\Facades\Route;

// CLEAR ALL COOKIES route
Route::get('/clear-cookies', function () {
    $response = response('All cookies cleared. <a href="/bypass-login">Click here to login</a>');
    
    // Clear all possible cookies
    $cookies = ['si-majter-session', 'laravel_session', 'XSRF-TOKEN', 'remember_web'];
    foreach ($cookies as $cookieName) {
        $response->withCookie(cookie()->forget($cookieName));
    }
    
    return $response;
});

// Dummy email verification routes to prevent errors
Route::get('/admin/email-verification/prompt', function () {
    return redirect('/admin');
})->name('filament.admin.auth.email-verification.prompt');

Route::get('/admin/email/verify', function () {
    return redirect('/admin');
})->name('verification.notice');

// BYPASS route - clear cookies and login
Route::get('/bypass-login', function () {
    // Get user first
    $user = \App\Models\User::where('email', 'admin@adamjaya.com')->first();
    
    if (!$user) {
        return 'User not found';
    }
    
    // Force mark email as verified
    $user->forceFill([
        'email_verified_at' => now(),
    ])->saveQuietly();
    
    // Clear old auth
    auth()->logout();
    
    // Start fresh session (don't invalidate, just flush old data)
    session()->flush();
    session()->regenerate(true); // Destroy old session ID
    
    // Login user
    auth()->guard('web')->login($user, true); // Remember = true
    
    // Explicitly save session
    session()->save();
    
    \Log::info('=== BYPASS LOGIN SUCCESS ===', [
        'auth_check' => auth()->check(),
        'auth_guard_web' => auth()->guard('web')->check(),
        'user' => auth()->user() ? auth()->user()->email : null,
        'session_id' => session()->getId(),
        'session_keys' => array_keys(session()->all()),
        'has_login_key' => session()->has('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'),
    ]);
    
    // Return HTML with manual link
    return response('
        <!DOCTYPE html>
        <html>
        <head>
            <title>Login Successful</title>
            <style>
                body { font-family: Arial; text-align: center; padding: 50px; }
                .success { color: green; font-size: 24px; margin: 20px; }
                .link { font-size: 18px; }
                a { color: #4F46E5; text-decoration: none; padding: 10px 20px; background: #E0E7FF; border-radius: 5px; display: inline-block; }
                a:hover { background: #C7D2FE; }
                .info { color: #666; font-size: 14px; margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class="success">✓ Login Successful!</div>
            <div class="link">
                <a href="/admin">Go to Dashboard →</a>
            </div>
            <div class="info">
                <p>User: ' . $user->email . '</p>
                <p>Session ID: ' . session()->getId() . '</p>
                <p>Auth Check: ' . (auth()->check() ? 'TRUE' : 'FALSE') . '</p>
            </div>
        </body>
        </html>
    ');
});

// Debug route untuk check session & auth
Route::get('/debug-auth', function () {
    $user = \App\Models\User::where('email', 'admin@adamjaya.com')->first();
    
    return response()->json([
        'auth_check' => auth()->check(),
        'auth_user' => auth()->user() ? auth()->user()->email : null,
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
        'user_exists' => $user ? true : false,
        'user_active' => $user ? $user->is_active : null,
        'guard' => config('filament.auth.guard'),
        'session_driver' => config('session.driver'),
    ]);
});

Route::get('/test-session', function () {
    // Set a test value in session
    session(['test_key' => 'test_value_' . time()]);
    session()->save();
    
    return response()->json([
        'message' => 'Session set',
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
    ]);
});

Route::get('/test-session-read', function () {
    return response()->json([
        'message' => 'Session read',
        'session_id' => session()->getId(),
        'test_key' => session('test_key'),
        'session_data' => session()->all(),
    ]);
});

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/admin');
    }
    return redirect('/admin/login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/company/select', [CompanySelectionController::class, 'show'])
        ->name('company.select');
    Route::post('/company/select', [CompanySelectionController::class, 'store'])
        ->name('company.store');

    // PDF Routes
    Route::prefix('pdf')->group(function () {
        // Price Quotation PDF
        Route::get('/price-quotation/{priceQuotation}/download', [PDFController::class, 'downloadPriceQuotation'])
            ->name('pdf.price-quotation.download');
        Route::get('/price-quotation/{priceQuotation}/preview', [PDFController::class, 'previewPriceQuotation'])
            ->name('pdf.price-quotation.preview');

        // Purchase Order PDF
        Route::get('/purchase-order/{purchaseOrder}/download', [PDFController::class, 'downloadPurchaseOrder'])
            ->name('pdf.purchase-order.download');
        Route::get('/purchase-order/{purchaseOrder}/preview', [PDFController::class, 'previewPurchaseOrder'])
            ->name('pdf.purchase-order.preview');

        // Delivery Note PDF
        Route::get('/delivery-note/{deliveryNote}/download', [PDFController::class, 'downloadDeliveryNote'])
            ->name('pdf.delivery-note.download');
        Route::get('/delivery-note/{deliveryNote}/preview', [PDFController::class, 'previewDeliveryNote'])
            ->name('pdf.delivery-note.preview');

        // Invoice PDF
        Route::get('/invoice/{invoice}/download', [PDFController::class, 'downloadInvoice'])
            ->name('pdf.invoice.download');
        Route::get('/invoice/{invoice}/preview', [PDFController::class, 'previewInvoice'])
            ->name('pdf.invoice.preview');
        
        // Receipt/Kwitansi PDF
        Route::get('/receipt/{payment}/download', [PDFController::class, 'downloadReceipt'])
            ->name('pdf.receipt.download');
        Route::get('/receipt/{payment}/preview', [PDFController::class, 'previewReceipt'])
            ->name('pdf.receipt.preview');
        
        // Sales Report PDF Summary
        Route::get('/sales-report/summary', [PDFController::class, 'salesReportSummary'])
            ->name('pdf.sales-report.summary');
    });
});
