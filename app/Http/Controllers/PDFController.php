<?php

namespace App\Http\Controllers;

use App\Models\PriceQuotation;
use App\Models\PurchaseOrder;
use App\Models\DeliveryNote;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFController extends Controller
{
    /**
     * Sanitize filename by replacing invalid characters
     */
    private function sanitizeFilename($filename)
    {
        return str_replace(['/', '\\'], '-', $filename);
    }

    public function downloadPriceQuotation(PriceQuotation $priceQuotation)
    {
        $priceQuotation->load(['supplier', 'items.product', 'company']);
        $pdf = Pdf::loadView('pdf.price-quotation', compact('priceQuotation'));
        $filename = $this->sanitizeFilename('Penawaran_Harga_' . $priceQuotation->quotation_number . '.pdf');
        
        return $pdf->download($filename);
    }

    public function downloadPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.product', 'company']);
        $pdf = Pdf::loadView('pdf.purchase-order', compact('purchaseOrder'));
        $filename = $this->sanitizeFilename('Purchase_Order_' . $purchaseOrder->po_number . '.pdf');
        
        return $pdf->download($filename);
    }

    public function downloadDeliveryNote(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['customer', 'items.product', 'company', 'invoice']);
        $pdf = Pdf::loadView('pdf.delivery-note', compact('deliveryNote'));
        $filename = $this->sanitizeFilename('Surat_Jalan_' . $deliveryNote->delivery_note_number . '.pdf');
        
        return $pdf->download($filename);
    }

    public function downloadInvoice(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product', 'company', 'deliveryNote']);
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));
        $filename = $this->sanitizeFilename('Invoice_' . $invoice->invoice_number . '.pdf');
        
        return $pdf->download($filename);
    }

    public function previewPriceQuotation(PriceQuotation $priceQuotation)
    {
        $priceQuotation->load(['supplier', 'items.product', 'company']);
        $pdf = Pdf::loadView('pdf.price-quotation', compact('priceQuotation'));
        $filename = $this->sanitizeFilename('Penawaran_Harga_' . $priceQuotation->quotation_number . '.pdf');
        
        return $pdf->stream($filename);
    }

    public function previewPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.product', 'company']);
        $pdf = Pdf::loadView('pdf.purchase-order', compact('purchaseOrder'));
        $filename = $this->sanitizeFilename('Purchase_Order_' . $purchaseOrder->po_number . '.pdf');
        
        return $pdf->stream($filename);
    }

    public function previewDeliveryNote(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['customer', 'items.product', 'company', 'invoice']);
        $pdf = Pdf::loadView('pdf.delivery-note', compact('deliveryNote'));
        $filename = $this->sanitizeFilename('Surat_Jalan_' . $deliveryNote->delivery_note_number . '.pdf');
        
        return $pdf->stream($filename);
    }

    public function previewInvoice(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product', 'company', 'deliveryNote']);
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));
        $filename = $this->sanitizeFilename('Invoice_' . $invoice->invoice_number . '.pdf');
        
        return $pdf->stream($filename);
    }

    public function salesReportSummary(Request $request)
    {
        // TODO: Implement sales report summary PDF
        return response()->json(['message' => 'Sales Report Summary - Coming Soon']);
    }
}