<?php

namespace App\Filament\Widgets;

use App\Models\DeliveryNote;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentDeliveryNotesWidget extends BaseWidget
{
    protected static ?string $heading = 'Surat Jalan Terbaru';
    protected static ?int $sort = 4;
    protected static ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        $companyId = session('selected_company_id');

        return $table
            ->query(
                DeliveryNote::where('company_id', $companyId)
                    ->with(['customer'])
                    ->latest('delivery_date')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('sj_number')
                    ->label('No. SJ')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('delivery_date')
                    ->label('Tgl Kirim')
                    ->date('d/m/Y')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'Draft',
                        'primary' => 'Sent',
                        'success' => 'Completed',
                    ]),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->colors([
                        'success' => 'PPN',
                        'info' => 'Non-PPN',
                    ]),
            ])
            ->paginated(false);
    }
}
