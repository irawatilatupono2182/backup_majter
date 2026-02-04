<?php

namespace App\Filament\Resources\PayableResource\RelationManagers;

use App\Models\PayablePayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    
    protected static ?string $title = 'Riwayat Pembayaran';
    
    protected static ?string $recordTitleAttribute = 'payment_number';

    public function form(Form $form): Form
    {
        $companyId = session('selected_company_id');
        $payable = $this->getOwnerRecord();
        
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pembayaran')
                    ->schema([
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->default(now())
                            ->required()
                            ->displayFormat('d/m/Y')
                            ->native(false)
                            ->maxDate(now()),
                        
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah Bayar')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->inputMode('decimal')
                            ->step('0.01')
                            ->maxValue($payable->remaining_amount)
                            ->helperText(fn() => 'Sisa hutang: Rp ' . number_format($payable->remaining_amount, 0, ',', '.'))
                            ->formatStateUsing(fn($state) => $state ? number_format($state, 2, '.', '') : '')
                            ->dehydrateStateUsing(fn($state) => $state ? floatval(str_replace(',', '', $state)) : 0),
                        
                        Forms\Components\Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'cash' => '💵 Tunai',
                                'transfer' => '🏦 Transfer Bank',
                                'check' => '📝 Cek',
                                'giro' => '📋 Giro',
                                'other' => '➕ Lainnya',
                            ])
                            ->default('transfer')
                            ->required()
                            ->live(),
                        
                        Forms\Components\Hidden::make('company_id')
                            ->default($companyId),
                        
                        Forms\Components\Hidden::make('payable_id')
                            ->default($payable->payable_id),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Detail Bank')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->label('Nama Bank')
                            ->maxLength(255)
                            ->placeholder('Contoh: BCA, Mandiri, BRI'),
                        
                        Forms\Components\TextInput::make('account_number')
                            ->label('Nomor Rekening')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('check_giro_number')
                            ->label('Nomor Cek/Giro')
                            ->maxLength(255)
                            ->visible(fn(Forms\Get $get) => in_array($get('payment_method'), ['check', 'giro'])),
                    ])
                    ->columns(2)
                    ->visible(fn(Forms\Get $get) => in_array($get('payment_method'), ['transfer', 'check', 'giro']))
                    ->collapsible(),
                
                Forms\Components\Section::make('Upload Bukti Pembayaran')
                    ->description('Upload bukti transfer, foto struk, atau dokumen pembayaran lainnya')
                    ->schema([
                        Forms\Components\FileUpload::make('attachment_path')
                            ->label('File Bukti')
                            ->disk('public')
                            ->directory('payable-payments/attachments')
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
                            ->label('Catatan Pembayaran')
                            ->placeholder('Tambahkan catatan jika diperlukan...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('payment_number')
            ->defaultSort('payment_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('payment_number')
                    ->label('No. Pembayaran')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->money('IDR')
                        ->label('Total Dibayar')),
                
                Tables\Columns\TextColumn::make('payment_method_label')
                    ->label('Metode')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('-'),
                
                Tables\Columns\IconColumn::make('attachment_path')
                    ->label('Bukti')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => '💵 Tunai',
                        'transfer' => '🏦 Transfer Bank',
                        'check' => '📝 Cek',
                        'giro' => '📋 Giro',
                        'other' => '➕ Lainnya',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Pembayaran')
                    ->icon('heroicon-o-plus')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Pembayaran berhasil dicatat')
                            ->body('Data pembayaran telah disimpan dan saldo hutang diperbarui.')
                    )
                    ->after(function () {
                        Notification::make()
                            ->success()
                            ->title('Status hutang diperbarui')
                            ->body('Sisa hutang telah dihitung ulang.')
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Pembayaran diperbarui')
                                ->body('Data pembayaran telah diubah.')
                        ),
                    
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
                    
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Pembayaran dihapus')
                                ->body('Data pembayaran telah dihapus dan saldo hutang diperbarui.')
                        ),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('Belum ada pembayaran')
            ->emptyStateDescription('Buat pembayaran baru dengan klik tombol di atas.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }
}
