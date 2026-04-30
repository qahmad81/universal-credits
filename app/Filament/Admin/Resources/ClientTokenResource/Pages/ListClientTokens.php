<?php

namespace App\Filament\Admin\Resources\ClientTokenResource\Pages;

use App\Filament\Admin\Resources\ClientTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClientTokens extends ListRecords
{
    protected static string $resource = ClientTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
