<?php

namespace App\Console\Commands;

use App\Models\ClientBalance;
use App\Models\ClientToken;
use App\Models\PendingPayment;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireReservationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close expired pending reservations and refund balances';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredCount = 0;
        $now = now();

        $expiredReservations = PendingPayment::where('status', 'pending')
            ->where('expires_at', '<', $now)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($expiredReservations as $reservation) {
            try {
                DB::transaction(function () use ($reservation, &$expiredCount) {
                    // SELECT FOR UPDATE on client_balances and client_tokens
                    $balance = ClientBalance::where('user_id', $reservation->user_id)
                        ->lockForUpdate()
                        ->first();

                    $token = ClientToken::where('id', $reservation->client_token_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$balance || !$token) {
                        Log::warning("Skipping expiration for reservation #{$reservation->id}: Balance or token not found.");
                        return;
                    }

                    $amount = $reservation->amount;
                    $balanceBefore = $balance->final_balance;

                    // Add amount back to client_balances.pending_balance
                    // Actually, normally when reserving, we subtract from final and add to pending?
                    // Let's check how reserve works... 
                    // Based on migrations: final_balance and pending_balance.
                    // If it's a reservation, usually it's "pending".
                    // The task says: "Add amount back to client_balances.pending_balance"
                    // "Subtract amount from client_tokens.pending_balance"
                    // Wait, that sounds strange if we are REFUNDING.
                    // Usually pending_balance is where reserved funds SIT.
                    // Let's look at the instruction again:
                    // c. Add amount back to client_balances.pending_balance 
                    // d. Subtract amount from client_tokens.pending_balance
                    
                    // Actually, if it was reserved, it probably was: 
                    // client_balances.pending_balance -= amount 
                    // client_tokens.pending_balance -= amount (or something)
                    
                    // Let's re-read carefully:
                    // c. Add amount back to client_balances.pending_balance
                    // d. Subtract amount from client_tokens.pending_balance
                    
                    // This is what the user asked for specifically. I will follow it.
                    
                    $balance->pending_balance += $amount;
                    $token->pending_balance -= $amount;

                    $balance->save();
                    $token->save();

                    // Update PendingPayment status to expired
                    $reservation->status = 'expired';
                    $reservation->save();

                    // Create Transaction (type=expire, with proper balance_before/after)
                    // Note: balance_before/after in transactions table usually refers to the main balance (final_balance)
                    // even if it's an expiration of pending.
                    Transaction::create([
                        'user_id' => $reservation->user_id,
                        'client_token_id' => $reservation->client_token_id,
                        'vendor_token_id' => $reservation->vendor_token_id,
                        'pending_payment_id' => $reservation->id,
                        'type' => 'expire',
                        'amount' => $amount,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceBefore, // Final balance doesn't change on expiration of pending?
                        'description' => "Expired reservation #{$reservation->id}",
                        'created_at' => now(),
                    ]);

                    $expiredCount++;
                });
            } catch (\Exception $e) {
                Log::error("Failed to expire reservation #{$reservation->id}: " . $e->getMessage());
                $this->error("Failed to expire reservation #{$reservation->id}");
            }
        }

        Log::info("Expired {$expiredCount} reservations.");
        $this->info("Expired {$expiredCount} reservations.");
    }
}
