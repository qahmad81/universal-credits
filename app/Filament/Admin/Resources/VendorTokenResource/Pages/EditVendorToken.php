<?php

namespace App\Filament\Admin\Resources\VendorTokenResource\Pages;

use App\Filament\Admin\Resources\VendorTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVendorToken extends EditRecord
{
    protected static string $resource = VendorTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
