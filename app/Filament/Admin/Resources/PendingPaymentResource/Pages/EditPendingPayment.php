<?php

namespace App\Filament\Admin\Resources\PendingPaymentResource\Pages;

use App\Filament\Admin\Resources\PendingPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendingPayment extends EditRecord
{
    protected static string $resource = PendingPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
