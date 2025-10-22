<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesReportResource\Pages;
use App\Models\Invoice;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;

class SalesReportResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Penjualan';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'sales-report';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('invoice_number')
                    ->label('Nomor Invoice')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->description(function ($record) {
                        return $record->customer && $record->customer->is_ppn 
                            ? '✅ PPN' 
                            : '❌ Non-PPN';
                    }),

                TextColumn::make('customer.is_ppn')
                    ->label('PPN Customer')
                    ->formatStateUsing(function ($state) {
                        return $state ? '✅ Ya' : '❌ Tidak';
                    })
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_amount')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->money('IDR')),

                TextColumn::make('ppn_amount')
                    ->label('PPN')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->money('IDR')),

                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->money('IDR')),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(function (string $state): string {
                        if ($state === 'paid') return 'success';
                        if ($state === 'partial') return 'warning';
                        if ($state === 'overdue') return 'danger';
                        return 'gray';
                    })
                    ->formatStateUsing(function (string $state): string {
                        if ($state === 'paid') return 'Lunas';
                        if ($state === 'partial') return 'Sebagian';
                        if ($state === 'overdue') return 'Jatuh Tempo';
                        return 'Belum Lunas';
                    }),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->filters([
                Filter::make('invoice_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('invoice_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('invoice_date', '<=', $date),
                            );
                    }),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'unpaid' => 'Belum Lunas',
                        'partial' => 'Sebagian',
                        'paid' => 'Lunas',
                        'overdue' => 'Jatuh Tempo',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value']) && $data['value'] !== '') {
                            // Case-insensitive comparison to handle both 'paid' and 'Paid'
                            return $query->whereRaw('LOWER(status) = ?', [strtolower($data['value'])]);
                        }
                        return $query;
                    }),

                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('is_ppn')
                    ->label('Jenis Customer')
                    ->options([
                        '1' => 'Customer PPN',
                        '0' => 'Customer Non-PPN',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            return $query->whereHas('customer', function ($q) use ($data) {
                                $q->where('is_ppn', $data['value']);
                            });
                        }
                        return $query;
                    }),
            ])
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $companyId = auth()->user()->currentCompany ? auth()->user()->currentCompany->company_id : null;
                        return Excel::download(new SalesReportExport($companyId), 'laporan-penjualan-' . date('Y-m-d') . '.xlsx');
                    }),

                Action::make('print_summary')
                    ->label('Print Summary')
                    ->icon('heroicon-o-printer')
                    ->url(fn() => route('pdf.sales-report.summary'))
                    ->openUrlInNewTab(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesReports::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('company_id', session('selected_company_id'))
            ->with(['customer', 'payments']);
    }
}