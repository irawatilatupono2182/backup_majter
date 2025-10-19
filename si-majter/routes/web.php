<?php

use App\Http\Controllers\CompanySelectionController;
use App\Http\Controllers\PDFController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
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
    });
});
