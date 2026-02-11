<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = '💼 Penjualan';
    
    protected static ?int $navigationSort = 1;

    public static function getNavigationTooltip(): ?string
    {
        return 'Data customer/pelanggan';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Customer')
                    ->schema([
                        Forms\Components\Hidden::make('company_id')
                            ->default(fn() => session('selected_company_id')),
                        Forms\Components\TextInput::make('customer_code')
                            ->label('Kode Customer')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Customer')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_person')
                            ->label('Contact Person (U.P.)')
                            ->maxLength(100),
                        Forms\Components\Toggle::make('is_ppn')
                            ->label('Customer PPN')
                            ->default(function () {
                                $type = session('customer_type_create');
                                return $type === 'PPN';
                            })
                            ->disabled(fn ($context) => $context === 'create' && session('customer_type_create') !== null)
                            ->dehydrated(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ]),
                Forms\Components\Section::make('Alamat')
                    ->schema([
                        Forms\Components\Textarea::make('address_ship_to')
                            ->label('Alamat Kirim (SHIP TO)')
                            ->required()
                            ->rows(3),
                        Forms\Components\Textarea::make('address_bill_to')
                            ->label('Alamat Tagihan (BILL TO)')
                            ->rows(3),
                        Forms\Components\TextInput::make('city')
                            ->label('Kota')
                            ->placeholder('Contoh: BANDUNG')
                            ->maxLength(100)
                            ->helperText('Kota customer, akan muncul di Surat Jalan'),
                    ]),
                Forms\Components\Section::make('Kontak & Administrasi')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->maxLength(30),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('npwp')
                            ->label('NPWP')
                            ->maxLength(30),
                        Forms\Components\TextInput::make('billing_schedule')
                            ->label('Jadwal Kontra Bon')
                            ->placeholder('Contoh: Setiap tgl 5, Minggu ke-2')
                            ->maxLength(100),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->where('company_id', session('selected_company_id')))
            ->columns([
                Tables\Columns\TextColumn::make('customer_code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_person')
                    ->label('Contact Person'),
                Tables\Columns\TextColumn::make('city')
                    ->label('Kota')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_ppn')
                    ->label('PPN')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status'),
                Tables\Filters\TernaryFilter::make('is_ppn')
                    ->label('PPN'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}