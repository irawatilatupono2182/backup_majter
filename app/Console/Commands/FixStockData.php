<?php

namespace App\Console\Commands;

use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixStockData extends Command
{
    protected $signature = 'stock:fix {company_id?}';
    protected $description = 'Fix and recalculate stock data based on stock movements';

    public function handle()
    {
        $companyId = $this->argument('company_id') ?: session('selected_company_id');
        
        if (!$companyId) {
            $this->error('Company ID is required!');
            return 1;
        }

        $this->info("Fixing stock data for company: $companyId");

        // Get all stocks for this company
        $stocks = Stock::where('company_id', $companyId)->get();
        
        foreach ($stocks as $stock) {
            // Calculate actual stock from movements
            $stockIn = StockMovement::where('company_id', $companyId)
                ->where('product_id', $stock->product_id)
                ->where('movement_type', 'in')
                ->sum('quantity');

            $stockOut = StockMovement::where('company_id', $companyId)
                ->where('product_id', $stock->product_id)
                ->where('movement_type', 'out')
                ->sum('quantity');

            $calculatedStock = $stockIn - $stockOut;

            $this->line("Product: {$stock->product->name}");
            $this->line("  Current Stock: {$stock->quantity}");
            $this->line("  Stock In: $stockIn");
            $this->line("  Stock Out: $stockOut");
            $this->line("  Calculated Stock: $calculatedStock");

            // Update stock
            $stock->quantity = $calculatedStock;
            $stock->available_quantity = $calculatedStock - $stock->reserved_quantity;
            $stock->save();

            $this->info("  Updated stock to: $calculatedStock\n");
        }

        // Remove duplicate stocks for same product
        $this->info("\nChecking for duplicate stocks...");
        
        $duplicates = Stock::select('company_id', 'product_id', DB::raw('COUNT(*) as count'))
            ->where('company_id', $companyId)
            ->groupBy('company_id', 'product_id')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            $this->warn("Found duplicate stocks for product_id: {$duplicate->product_id}");
            
            // Get all stocks for this product
            $productStocks = Stock::where('company_id', $companyId)
                ->where('product_id', $duplicate->product_id)
                ->orderBy('created_at', 'asc')
                ->get();

            // Keep the first one, merge others
            $mainStock = $productStocks->first();
            $totalQty = 0;

            foreach ($productStocks as $pStock) {
                $totalQty += $pStock->quantity;
                
                if ($pStock->stock_id !== $mainStock->stock_id) {
                    $this->line("  Deleting duplicate stock ID: {$pStock->stock_id}");
                    $pStock->delete();
                }
            }

            // Update main stock with total
            $mainStock->quantity = $totalQty;
            $mainStock->available_quantity = $totalQty - $mainStock->reserved_quantity;
            $mainStock->save();

            $this->info("  Merged duplicates. Final stock: $totalQty\n");
        }

        // Remove negative stocks
        $this->info("\nRemoving negative stocks...");
        $negativeStocks = Stock::where('company_id', $companyId)
            ->where('quantity', '<', 0)
            ->get();

        foreach ($negativeStocks as $negStock) {
            $this->warn("Found negative stock for product: {$negStock->product->name} (Qty: {$negStock->quantity})");
            $this->line("  Setting to 0...");
            $negStock->quantity = 0;
            $negStock->available_quantity = 0;
            $negStock->save();
        }

        $this->info("\n✅ Stock data fixed successfully!");
        
        return 0;
    }
}
