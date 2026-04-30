<?php

namespace App\Filament\Admin\Resources\ClientTokenResource\Pages;

use App\Filament\Admin\Resources\ClientTokenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClientToken extends EditRecord
{
    protected static string $resource = ClientTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
