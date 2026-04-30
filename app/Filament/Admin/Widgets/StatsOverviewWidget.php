<?php

namespace App\Filament\Admin\Widgets;

use App\Models\ClientBalance;
use App\Models\ClientToken;
use App\Models\VendorToken;
use App\Models\PendingPayment;
use App\Services\UCMask;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalBalanceDb = ClientBalance::sum('final_balance');
        $totalBalanceUc = UCMask::toDisplay(UCMask::fromDb($totalBalanceDb));

        return [
            Stat::make('Total System Balance', $totalBalanceUc . ' UC')
                ->description('Sum of all client balances')
                ->icon('heroicon-o-banknotes'),
            Stat::make('Active Client Tokens', ClientToken::where('status', 'active')->count())
                ->icon('heroicon-o-key'),
            Stat::make('Active Vendor Tokens', VendorToken::where('status', 'active')->count())
                ->icon('heroicon-o-shield-check'),
            Stat::make('Pending Reservations', PendingPayment::where('status', 'pending')->count())
                ->icon('heroicon-o-clock'),
        ];
    }
}
