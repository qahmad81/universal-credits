<?php

namespace App\Filament\Admin\Resources\VendorTokenResource\Pages;

use App\Filament\Admin\Resources\VendorTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVendorTokens extends ListRecords
{
    protected static string $resource = VendorTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
