<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ClientTokenResource\Pages;
use App\Filament\Admin\Resources\ClientTokenResource\RelationManagers;
use App\Models\ClientToken;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientTokenResource extends Resource
{
    protected static ?string $model = ClientToken::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('token_hash')
                    ->required()
                    ->maxLength(64),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('limit_balance')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('final_balance')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('pending_balance')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('token_hash')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('limit_balance')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('final_balance')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pending_balance')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListClientTokens::route('/'),
            'create' => Pages\CreateClientToken::route('/create'),
            'edit' => Pages\EditClientToken::route('/{record}/edit'),
        ];
    }
}
