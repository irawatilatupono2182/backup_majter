<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayableResource\Pages;
use App\Filament\Resources\PayableResource\RelationManagers\PaymentsRelationManager;
use App\Models\Payable;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class PayableResource extends Resource
{
    protected static ?string $model = Payable::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationLabel = 'Hutang';
    
    protected static ?string $pluralModelLabel = 'Hutang';
    
    protected static ?string $modelLabel = 'Hutang';
    
    protected static ?string $navigationGroup = '📈 Laporan';
    
    protected static ?int $navigationSort = 4;
    
    public static function getNavigationBadge(): ?string
    {
        $companyId = session('selected_company_id');
        if (!$companyId) return null;
        
        return (string) Payable::where('company_id', $companyId)
            ->whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getEloquentQuery(): Builder
    {
        $companyId = session('selected_company_id');
        
        return parent::getEloquentQuery()
            ->when($companyId, fn($query) => $query->where('company_id', $companyId))
            ->with(['supplier', 'purchaseOrder', 'payments']);
    }

    public static function form(Form $form): Form
    {
        $companyId = session('selected_company_id');
        
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Hutang')
                    ->schema([
                        Forms\Components\Select::make('reference_type')
                            ->label('Tipe Referensi')
                            ->options([
                                'po' => '📋 Dari Purchase Order',
                                'manual' => '✍️ Input Manual',
                            ])
                            ->default('manual')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state === 'po') {
                                    $set('reference_number', null);
                                    $set('reference_description', null);
                                } else {
                                    $set('purchase_order_id', null);
                                }
                            })
                            ->columnSpanFull(),
                        
                        // Untuk referensi PO
                        Forms\Components\Select::make('purchase_order_id')
                            ->label('Purchase Order')
                            ->options(function () use ($companyId) {
                                return PurchaseOrder::where('company_id', $companyId)
                                    ->whereNotNull('due_date')
                                    ->with('supplier')
                                    ->get()
                                    ->mapWithKeys(fn($po) => [
                                        $po->po_id => "{$po->po_number} - {$po->supplier->name} (Rp " . number_format($po->grand_total ?? 0, 0, ',', '.') . ")"
                                    ]);
                            })
                            ->searchable()
                            ->preload()
                            ->visible(fn(Get $get) => $get('reference_type') === 'po')
                            ->required(fn(Get $get) => $get('reference_type') === 'po')
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) use ($companyId) {
                                if ($state) {
                                    $po = PurchaseOrder::find($state);
                                    if ($po) {
                                        $set('supplier_id', $po->supplier_id);
                                        $set('amount', $po->grand_total ?? 0);
                                        $set('payable_date', $po->order_date);
                                        $set('due_date', $po->due_date);
                                    }
                                }
                            })
                            ->columnSpanFull(),
                        
                        // Untuk referensi manual
                        Forms\Components\TextInput::make('reference_number')
                            ->label('Nomor Referensi')
                            ->placeholder('Contoh: INV/2024/001, NOTA-123')
                            ->visible(fn(Get $get) => $get('reference_type') === 'manual')
                            ->required(fn(Get $get) => $get('reference_type') === 'manual')
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('reference_description')
                            ->label('Deskripsi Referensi')
                            ->placeholder('Jelaskan detail hutang ini...')
                            ->visible(fn(Get $get) => $get('reference_type') === 'manual')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(function () use ($companyId) {
                                return Supplier::where('company_id', $companyId)
                                    ->pluck('name', 'supplier_id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn(Get $get) => $get('reference_type') === 'po' && $get('purchase_order_id')),
                        
                        Forms\Components\Hidden::make('company_id')
                            ->default($companyId),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Detail Hutang')
                    ->schema([
                        Forms\Components\DatePicker::make('payable_date')
                            ->label('Tanggal Hutang')
                            ->default(now())
                            ->required()
                            ->displayFormat('d/m/Y')
                            ->native(false),
                        
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Jatuh Tempo')
                            ->required()
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->minDate(fn(Get $get) => $get('payable_date'))
                            ->helperText('Tanggal harus bayar hutang'),
                        
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah Hutang')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->inputMode('decimal')
                            ->step('0.01')
                            ->formatStateUsing(fn($state) => $state ? number_format($state, 2, '.', '') : '')
                            ->dehydrateStateUsing(fn($state) => $state ? floatval(str_replace(',', '', $state)) : 0),
                        
                        Forms\Components\Placeholder::make('paid_amount_display')
                            ->label('Sudah Dibayar')
                            ->content(fn($record) => $record ? 'Rp ' . number_format($record->paid_amount, 0, ',', '.') : 'Rp 0')
                            ->visible(fn($record) => $record !== null),
                        
                        Forms\Components\Placeholder::make('remaining_amount_display')
                            ->label('Sisa Hutang')
                            ->content(fn($record) => $record ? 'Rp ' . number_format($record->remaining_amount, 0, ',', '.') : '-')
                            ->visible(fn($record) => $record !== null),
                        
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'unpaid' => '⚪ Belum Dibayar',
                                'partial' => '🟡 Dibayar Sebagian',
                                'paid' => '🟢 Lunas',
                                'overdue' => '🔴 Terlambat',
                            ])
                            ->default('unpaid')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn($record) => $record !== null),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Upload Bukti Hutang')
                    ->description('Upload dokumen pendukung seperti invoice, nota, atau dokumen lainnya')
                    ->schema([
                        Forms\Components\FileUpload::make('attachment_path')
                            ->label('File Bukti')
                            ->disk('public')
                            ->directory('payables/attachments')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(5120) // 5MB
                            ->downloadable()
                            ->openable()
                            ->previewable()
                            ->helperText('Format: PDF atau gambar. Maksimal 5MB.')
                            ->storeFileNamesIn('attachment_filename')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Catatan')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Tambahan')
                            ->placeholder('Tambahkan catatan jika diperlukan...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('payable_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('payable_number')
                    ->label('No. Hutang')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Nomor disalin!'),
                
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-storefront'),
                
                Tables\Columns\TextColumn::make('reference_label')
                    ->label('Referensi')
                    ->searchable(['reference_number', 'purchase_order_id'])
                    ->badge()
                    ->color(fn($record) => $record->reference_type === 'po' ? 'info' : 'gray'),
                
                Tables\Columns\TextColumn::make('payable_date')
                    ->label('Tgl Hutang')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->badge()
                    ->color(function ($record) {
                        $days = $record->days_until_due;
                        if ($days < 0) return 'danger';
                        if ($days == 0) return 'warning';
                        if ($days <= 7) return 'info';
                        return 'success';
                    })
                    ->formatStateUsing(function ($record) {
                        $days = $record->days_until_due;
                        $date = $record->due_date->format('d/m/Y');
                        
                        if ($days < 0) {
                            return $date . ' (' . abs($days) . ' hari lalu)';
                        } elseif ($days == 0) {
                            return $date . ' (HARI INI)';
                        } elseif ($days <= 7) {
                            return $date . ' (' . $days . ' hari lagi)';
                        }
                        
                        return $date;
                    }),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->money('IDR')
                        ->label('Total')),
                
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Dibayar')
                    ->money('IDR')
                    ->sortable()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('Sisa')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->color('danger')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->money('IDR')
                        ->label('Total Sisa')),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'unpaid' => '⚪ Belum Dibayar',
                        'partial' => '🟡 Sebagian',
                        'paid' => '🟢 Lunas',
                        'overdue' => '🔴 Terlambat',
                        default => $state,
                    })
                    ->color(fn($state) => match($state) {
                        'unpaid' => 'gray',
                        'partial' => 'warning',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('attachment_path')
                    ->label('File')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'unpaid' => '⚪ Belum Dibayar',
                        'partial' => '🟡 Dibayar Sebagian',
                        'paid' => '🟢 Lunas',
                        'overdue' => '🔴 Terlambat',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('reference_type')
                    ->label('Tipe Referensi')
                    ->options([
                        'po' => '📋 Dari PO',
                        'manual' => '✍️ Manual',
                    ]),
                
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('due_soon')
                    ->label('Jatuh Tempo < 7 Hari')
                    ->query(fn(Builder $query) => $query->where('due_date', '<=', now()->addDays(7))
                        ->whereIn('status', ['unpaid', 'partial'])),
                
                Tables\Filters\Filter::make('overdue')
                    ->label('Sudah Lewat Jatuh Tempo')
                    ->query(fn(Builder $query) => $query->where('status', 'overdue')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    
                    Tables\Actions\Action::make('download_attachment')
                        ->label('Download Bukti')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->visible(fn($record) => $record->attachment_path !== null)
                        ->action(function ($record) {
                            if ($record->attachment_path && Storage::disk('public')->exists($record->attachment_path)) {
                                return Storage::disk('public')->download(
                                    $record->attachment_path,
                                    $record->attachment_filename ?? basename($record->attachment_path)
                                );
                            }
                        }),
                    
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada data hutang')
            ->emptyStateDescription('Buat hutang baru dengan klik tombol di bawah.')
            ->emptyStateIcon('heroicon-o-credit-card');
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayables::route('/'),
            'create' => Pages\CreatePayable::route('/create'),
            'edit' => Pages\EditPayable::route('/{record}/edit'),
            'view' => Pages\ViewPayable::route('/{record}'),
        ];
    }
    
    public static function getNavigationTooltip(): ?string
    {
        return 'Kelola hutang usaha dan pembayarannya';
    }
}
