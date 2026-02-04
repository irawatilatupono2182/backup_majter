<?php

namespace App\Filament\Resources\PayablePaymentResource\Pages;

use App\Filament\Resources\PayablePaymentResource;
use Filament\Resources\Pages\ListRecords;

class ListPayablePayments extends ListRecords
{
    protected static string $resource = PayablePaymentResource::class;

    public function getTitle(): string
    {
        return 'Riwayat Pembayaran Hutang';
    }
}
