<x-filament-panels::page>
    <div class="space-y-3" x-data="{ 
        openSections: ['overview'],
        toggleSection(section) {
            if (this.openSections.includes(section)) {
                this.openSections = this.openSections.filter(s => s !== section);
            } else {
                this.openSections.push(section);
            }
        },
        isOpen(section) {
            return this.openSections.includes(section);
        }
    }">
        
        {{-- OVERVIEW SECTION --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                type="button"
                @click="toggleSection('overview')"
                class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-500 rounded-lg">
                        <x-filament::icon icon="heroicon-o-chart-bar-square" class="w-5 h-5 text-white"/>
                    </div>
                    <div class="text-left">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Overview</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Key Performance Indicators</p>
                    </div>
                </div>
                <x-filament::icon 
                    icon="heroicon-o-chevron-down" 
                    class="w-4 h-4 text-gray-400 transition-transform duration-200"
                    x-bind:class="{ 'rotate-180': isOpen('overview') }"
                />
            </button>
            
            <div 
                x-show="isOpen('overview')"
                x-collapse
                class="px-4 pb-4"
            >
                @livewire(\App\Filament\Widgets\StatsOverviewWidget::class)
            </div>
        </div>

        {{-- FINANCE SECTION --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                type="button"
                @click="toggleSection('finance')"
                class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-green-500 rounded-lg">
                        <x-filament::icon icon="heroicon-o-banknotes" class="w-5 h-5 text-white"/>
                    </div>
                    <div class="text-left">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Finance & Accounting</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Piutang, Aging, Cash Flow</p>
                    </div>
                    @php
                        $overdueCount = \App\Models\Invoice::where('company_id', session('selected_company_id'))
                            ->where('status', 'Overdue')->count();
                    @endphp
                    @if($overdueCount > 0)
                        <span class="px-2 py-0.5 bg-red-500 text-white text-xs font-semibold rounded-full">
                            {{ $overdueCount }}
                        </span>
                    @endif
                </div>
                <x-filament::icon 
                    icon="heroicon-o-chevron-down" 
                    class="w-4 h-4 text-gray-400 transition-transform duration-200"
                    x-bind:class="{ 'rotate-180': isOpen('finance') }"
                />
            </button>
            
            <div 
                x-show="isOpen('finance')"
                x-collapse
                class="p-4 bg-gray-50 dark:bg-gray-900/50"
            >
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @livewire(\App\Filament\Widgets\FinanceStatsWidget::class)
                    @livewire(\App\Filament\Widgets\InvoiceStatusChart::class)
                    @livewire(\App\Filament\Widgets\AgingAnalysisChart::class)
                    @livewire(\App\Filament\Widgets\CashFlowChart::class)
                </div>
            </div>
        </div>

        {{-- SALES SECTION --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                type="button"
                @click="toggleSection('sales')"
                class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-cyan-500 rounded-lg">
                        <x-filament::icon icon="heroicon-o-shopping-cart" class="w-5 h-5 text-white"/>
                    </div>
                    <div class="text-left">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Sales & Revenue</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Top Products & Customers</p>
                    </div>
                </div>
                <x-filament::icon 
                    icon="heroicon-o-chevron-down" 
                    class="w-4 h-4 text-gray-400 transition-transform duration-200"
                    x-bind:class="{ 'rotate-180': isOpen('sales') }"
                />
            </button>
            
            <div 
                x-show="isOpen('sales')"
                x-collapse
                class="p-4 bg-gray-50 dark:bg-gray-900/50"
            >
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @livewire(\App\Filament\Widgets\SalesRevenueChart::class)
                    @livewire(\App\Filament\Widgets\RecentDeliveryNotesWidget::class)
                    @livewire(\App\Filament\Widgets\TopSellingProductsWidget::class)
                    @livewire(\App\Filament\Widgets\TopCustomersWidget::class)
                </div>
            </div>
        </div>

        {{-- WAREHOUSE SECTION --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                type="button"
                @click="toggleSection('warehouse')"
                class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-amber-500 rounded-lg">
                        <x-filament::icon icon="heroicon-o-cube-transparent" class="w-5 h-5 text-white"/>
                    </div>
                    <div class="text-left">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Warehouse & Inventory</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Stock & Expiry Alerts</p>
                    </div>
                    @php
                        $alertCount = \App\Models\Stock::where('company_id', session('selected_company_id'))
                            ->where(function($q) {
                                $q->whereColumn('available_quantity', '<', 'minimum_stock')
                                  ->orWhere('expiry_date', '<', now()->addDays(30));
                            })->count();
                    @endphp
                    @if($alertCount > 0)
                        <span class="px-2 py-0.5 bg-amber-500 text-white text-xs font-semibold rounded-full">
                            {{ $alertCount }}
                        </span>
                    @endif
                </div>
                <x-filament::icon
                    icon="heroicon-o-chevron-down"
                    class="w-4 h-4 text-gray-400 transition-transform duration-200"
                    x-bind:class="{ 'rotate-180': isOpen('warehouse') }"
                />
            </button>

            <div
                x-show="isOpen('warehouse')"
                x-collapse
                class="p-4 bg-gray-50 dark:bg-gray-900/50"
            >
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @livewire(\App\Filament\Widgets\WarehouseStatsWidget::class)
                    @livewire(\App\Filament\Widgets\InventoryAlertsWidget::class)
                </div>
            </div>
        </div>

        {{-- PURCHASING SECTION --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
            <button
                type="button"
                @click="toggleSection('purchasing')"
                class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-purple-500 rounded-lg">
                        <x-filament::icon icon="heroicon-o-clipboard-document-list" class="w-5 h-5 text-white"/>
                    </div>
                    <div class="text-left">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Purchasing & Procurement</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Purchase Orders & Suppliers</p>
                    </div>
                </div>
                <x-filament::icon
                    icon="heroicon-o-chevron-down"
                    class="w-4 h-4 text-gray-400 transition-transform duration-200"
                    x-bind:class="{ 'rotate-180': isOpen('purchasing') }"
                />
            </button>

            <div
                x-show="isOpen('purchasing')"
                x-collapse
                class="p-4 bg-gray-50 dark:bg-gray-900/50"
            >
                @livewire(\App\Filament\Widgets\PurchasingActivityWidget::class)
            </div>
        </div>

    </div>
</x-filament-panels::page>
