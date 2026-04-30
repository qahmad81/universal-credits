<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\VendorTokenResource\Pages;
use App\Filament\Admin\Resources\VendorTokenResource\RelationManagers;
use App\Models\VendorToken;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VendorTokenResource extends Resource
{
    protected static ?string $model = VendorToken::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('rate_limit_per_minute')
                    ->required()
                    ->numeric()
                    ->default(60),
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true),
                Forms\Components\TextInput::make('webhook_url')
                    ->maxLength(255)
                    ->url(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active'),
                Tables\Columns\TextColumn::make('rate_limit_per_minute')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('webhook_url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('generateToken')
                    ->label('Generate Token')
                    ->icon('heroicon-o-key')
                    ->requiresConfirmation()
                    ->action(function (VendorToken $record) {
                        $rawToken = \Illuminate\Support\Str::random(64);
                        $record->update([
                            'token_hash' => hash('sha256', $rawToken),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Token Generated Successfully')
                            ->body("Please copy this token. It will only be shown once: \n\n**{$rawToken}**")
                            ->persistent()
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendorTokens::route('/'),
            'create' => Pages\CreateVendorToken::route('/create'),
            'edit' => Pages\EditVendorToken::route('/{record}/edit'),
        ];
    }
}
