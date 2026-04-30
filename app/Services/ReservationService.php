<?php

namespace App\Services;

use App\Models\ClientBalance;
use App\Models\ClientToken;
use App\Models\PendingPayment;
use App\Models\Transaction;
use App\Models\VendorToken;
use App\Services\UCMask;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReservationService
{
    public function reserve(VendorToken $vendorToken, string $rawClientToken, float $amount, ?string $description = null)
    {
        $dbAmount = UCMask::toDb($amount);
        $clientTokenHash = hash('sha256', $rawClientToken);

        try {
            return DB::transaction(function () use ($vendorToken, $clientTokenHash, $dbAmount, $amount, $description) {
                // 1. Find client token and LOCK
                $clientToken = ClientToken::where('token_hash', $clientTokenHash)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (!$clientToken) {
                    throw new HttpException(404, 'Invalid client token');
                }

                // 2. Find client balance and LOCK
                $clientBalance = ClientBalance::where('user_id', $clientToken->user_id)
                    ->lockForUpdate()
                    ->first();

                if (!$clientBalance) {
                    throw new HttpException(500, 'Client balance record not found');
                }

                // 3. Check: client pending_balance - db_amount >= 0
                if ($clientBalance->pending_balance - $dbAmount < 0) {
                    throw new HttpException(402, 'Insufficient balance');
                }

                // 4. Check: client_token pending_balance + db_amount <= client_token limit_balance
                if ($clientToken->pending_balance + $dbAmount > $clientToken->limit_balance) {
                    throw new \App\Exceptions\TokenLimitReachedException($clientToken);
                }

                $oldBalance = $clientBalance->pending_balance;
                
                // 5. Update balances
                $clientBalance->decrement('pending_balance', $dbAmount);
                $clientToken->increment('pending_balance', $dbAmount);

                // 6. Create records
                $pendingPayment = PendingPayment::create([
                    'user_id' => $clientToken->user_id,
                    'vendor_token_id' => $vendorToken->id,
                    'client_token_id' => $clientToken->id,
                    'amount' => $dbAmount,
                    'description' => $description,
                    'expires_at' => Carbon::now()->addHour(),
                    'status' => 'pending',
                ]);

                Transaction::create([
                    'user_id' => $clientToken->user_id,
                    'vendor_token_id' => $vendorToken->id,
                    'client_token_id' => $clientToken->id,
                    'pending_payment_id' => $pendingPayment->id,
                    'type' => 'reserve',
                    'amount' => $dbAmount,
                    'balance_before' => $oldBalance,
                    'balance_after' => $oldBalance - $dbAmount,
                    'description' => $description,
                ]);

                return [
                    'reservation_id' => $pendingPayment->id,
                    'amount_reserved' => $amount,
                    'expires_at' => $pendingPayment->expires_at->toDateTimeString(),
                ];
            });
        } catch (\App\Exceptions\TokenLimitReachedException $e) {
            $e->getClientToken()->update(['is_active' => false]);
            throw new HttpException(402, 'Token limit reached');
        }
    }

    public function confirm(VendorToken $vendorToken, int $reservationId, float $actualAmount)
    {
        $actualAmountDb = UCMask::toDb($actualAmount);

        return DB::transaction(function () use ($vendorToken, $reservationId, $actualAmountDb, $actualAmount) {
            $pendingPayment = PendingPayment::where('id', $reservationId)
                ->where('vendor_token_id', $vendorToken->id)
                ->lockForUpdate()
                ->first();

            if (!$pendingPayment) {
                throw new HttpException(404, 'Reservation not found');
            }

            if ($pendingPayment->status !== 'pending') {
                throw new HttpException(409, 'Reservation is already ' . $pendingPayment->status);
            }

            if ($pendingPayment->expires_at->isPast()) {
                throw new HttpException(410, 'Reservation expired');
            }

            $warning = null;
            $chargedAmountDb = $actualAmountDb;
            if ($chargedAmountDb > $pendingPayment->amount) {
                $chargedAmountDb = $pendingPayment->amount;
                $warning = 'Actual amount exceeded reserved amount; capped at ' . UCMask::fromDb($pendingPayment->amount);
            }

            $refundDb = $pendingPayment->amount - $chargedAmountDb;

            // Lock balances and tokens
            $clientToken = ClientToken::where('id', $pendingPayment->client_token_id)->lockForUpdate()->first();
            $clientBalance = ClientBalance::where('user_id', $pendingPayment->user_id)->lockForUpdate()->first();

            $oldFinalBalance = $clientBalance->final_balance;

            // Update client_balances
            $clientBalance->increment('pending_balance', $refundDb);
            $clientBalance->decrement('final_balance', $chargedAmountDb);

            // Update client_tokens
            $clientToken->decrement('pending_balance', $pendingPayment->amount);
            $clientToken->increment('final_balance', $chargedAmountDb);

            $pendingPayment->update(['status' => 'confirmed']);

            Transaction::create([
                'user_id' => $pendingPayment->user_id,
                'vendor_token_id' => $vendorToken->id,
                'client_token_id' => $pendingPayment->client_token_id,
                'pending_payment_id' => $pendingPayment->id,
                'type' => 'confirm',
                'amount' => $chargedAmountDb,
                'balance_before' => $oldFinalBalance,
                'balance_after' => $oldFinalBalance - $chargedAmountDb,
            ]);

            return [
                'success' => true,
                'amount_charged' => UCMask::fromDb($chargedAmountDb),
                'refunded' => UCMask::fromDb($refundDb),
                'warning' => $warning,
            ];
        });
    }

    public function cancel(VendorToken $vendorToken, int $reservationId)
    {
        return DB::transaction(function () use ($vendorToken, $reservationId) {
            $pendingPayment = PendingPayment::where('id', $reservationId)
                ->where('vendor_token_id', $vendorToken->id)
                ->lockForUpdate()
                ->first();

            if (!$pendingPayment) {
                throw new HttpException(404, 'Reservation not found');
            }

            if ($pendingPayment->status !== 'pending') {
                throw new HttpException(409, 'Reservation is already ' . $pendingPayment->status);
            }

            if ($pendingPayment->expires_at->isPast()) {
                throw new HttpException(410, 'Reservation expired');
            }

            // Lock balances and tokens
            $clientToken = ClientToken::where('id', $pendingPayment->client_token_id)->lockForUpdate()->first();
            $clientBalance = ClientBalance::where('user_id', $pendingPayment->user_id)->lockForUpdate()->first();

            // Update balances
            $clientBalance->increment('pending_balance', $pendingPayment->amount);
            $clientToken->decrement('pending_balance', $pendingPayment->amount);

            $pendingPayment->update(['status' => 'cancelled']);

            Transaction::create([
                'user_id' => $pendingPayment->user_id,
                'vendor_token_id' => $vendorToken->id,
                'client_token_id' => $pendingPayment->client_token_id,
                'pending_payment_id' => $pendingPayment->id,
                'type' => 'cancel',
                'amount' => $pendingPayment->amount,
                'balance_before' => $clientBalance->final_balance,
                'balance_after' => $clientBalance->final_balance,
                'description' => 'Reservation cancelled',
            ]);

            return [
                'success' => true,
                'amount_refunded' => UCMask::fromDb($pendingPayment->amount),
            ];
        });
    }
}
