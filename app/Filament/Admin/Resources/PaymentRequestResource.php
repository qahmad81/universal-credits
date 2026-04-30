<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PaymentRequestResource\Pages;
use App\Models\PaymentRequest;
use App\Models\Transaction;
use App\Services\UCMask;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;

class PaymentRequestResource extends Resource
{
    protected static ?string $model = PaymentRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('payment_method_id')
                    ->relationship('paymentMethod', 'name')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('reference'),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('user_notes')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('admin_notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('paymentMethod.name'),
                TextColumn::make('amount')
                    ->formatStateUsing(fn ($state) => number_format(UCMask::fromDb($state)) . ' UC'),
                TextColumn::make('reference')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Action::make('approve')
                    ->action(function (PaymentRequest $record) {
                        if ($record->status !== 'pending') return;

                        $user = $record->user;
                        $balance = $user->clientBalance;
                        $balanceBefore = $balance->final_balance;

                        $balance->final_balance += $record->amount;
                        $balance->pending_balance += $record->amount;
                        $balance->save();

                        Transaction::create([
                            'user_id' => $user->id,
                            'type' => 'topup',
                            'amount' => $record->amount,
                            'balance_before' => $balanceBefore,
                            'balance_after' => $balance->final_balance,
                            'description' => "Manual Top-up Approved: " . number_format(UCMask::fromDb($record->amount)) . " UC",
                            'reference_id' => $record->id,
                            'created_at' => now(),
                        ]);

                        $record->update(['status' => 'approved']);

                        if ($user->phone) {
                            Log::info("WhatsApp notification: User {$user->name} topped up " . number_format(UCMask::fromDb($record->amount)) . " UC");
                        }
                    })
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (PaymentRequest $record): bool => $record->status === 'pending'),

                Action::make('reject')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->required(),
                    ])
                    ->action(function (PaymentRequest $record, array $data) {
                        if ($record->status !== 'pending') return;

                        $record->update([
                            'status' => 'rejected',
                            'admin_notes' => $data['admin_notes'],
                        ]);
                    })
                    ->requiresConfirmation()
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (PaymentRequest $record): bool => $record->status === 'pending'),
                
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePaymentRequests::route('/'),
        ];
    }
}
