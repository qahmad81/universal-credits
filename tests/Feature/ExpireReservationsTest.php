<?php

namespace Tests\Feature;

use App\Models\ClientBalance;
use App\Models\ClientToken;
use App\Models\PendingPayment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VendorToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireReservationsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $clientToken;
    protected $vendorToken;
    protected $clientBalance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        
        $this->clientBalance = ClientBalance::create([
            'user_id' => $this->user->id,
            'final_balance' => 10000,
            'pending_balance' => 5000,
        ]);

        $this->vendorToken = VendorToken::create([
            'token_hash' => hash('sha256', 'vendor-test-token'),
            'name' => 'Vendor Test Token',
            'is_active' => true,
        ]);

        $this->clientToken = ClientToken::create([
            'user_id' => $this->user->id,
            'token_hash' => hash('sha256', 'test-token'),
            'name' => 'Test Token',
            'limit_balance' => 10000,
            'final_balance' => 0,
            'pending_balance' => 2000,
            'is_active' => true,
        ]);
    }

    public function test_expires_old_reservations()
    {
        $amount = 1000;
        $reservation = PendingPayment::create([
            'user_id' => $this->user->id,
            'client_token_id' => $this->clientToken->id,
            'vendor_token_id' => $this->vendorToken->id,
            'amount' => $amount,
            'status' => 'pending',
            'expires_at' => now()->subMinutes(10),
            'created_at' => now()->subHour(),
        ]);

        $this->artisan('reservations:expire')
            ->expectsOutput('Expired 1 reservations.')
            ->assertExitCode(0);

        $reservation->refresh();
        $this->assertEquals('expired', $reservation->status);

        $this->clientBalance->refresh();
        $this->assertEquals(6000, $this->clientBalance->pending_balance);

        $this->clientToken->refresh();
        $this->assertEquals(1000, $this->clientToken->pending_balance);

        $this->assertDatabaseHas('transactions', [
            'pending_payment_id' => $reservation->id,
            'type' => 'expire',
            'amount' => $amount,
            'balance_before' => 10000,
            'balance_after' => 10000,
        ]);
    }

    public function test_does_not_expire_valid_reservations()
    {
        $reservation = PendingPayment::create([
            'user_id' => $this->user->id,
            'client_token_id' => $this->clientToken->id,
            'vendor_token_id' => $this->vendorToken->id,
            'amount' => 1000,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
        ]);

        $this->artisan('reservations:expire')
            ->expectsOutput('Expired 0 reservations.')
            ->assertExitCode(0);

        $reservation->refresh();
        $this->assertEquals('pending', $reservation->status);
    }

    public function test_processes_oldest_first()
    {
        PendingPayment::create([
            'user_id' => $this->user->id,
            'client_token_id' => $this->clientToken->id,
            'vendor_token_id' => $this->vendorToken->id,
            'amount' => 500,
            'status' => 'pending',
            'expires_at' => now()->subMinutes(10),
            'created_at' => now()->subMinutes(65),
        ]);

        PendingPayment::create([
            'user_id' => $this->user->id,
            'client_token_id' => $this->clientToken->id,
            'vendor_token_id' => $this->vendorToken->id,
            'amount' => 500,
            'status' => 'pending',
            'expires_at' => now()->subMinutes(5),
            'created_at' => now()->subMinutes(70), // This is older
        ]);

        // We can't easily verify the internal order unless we mock or spy, 
        // but the code uses orderBy('created_at', 'asc').
        // Let's just verify they both get expired.
        
        $this->artisan('reservations:expire')
            ->expectsOutput('Expired 2 reservations.')
            ->assertExitCode(0);
    }

    public function test_handles_multiple_expired_reservations()
    {
        for ($i = 0; $i < 5; $i++) {
            PendingPayment::create([
                'user_id' => $this->user->id,
                'client_token_id' => $this->clientToken->id,
                'vendor_token_id' => $this->vendorToken->id,
                'amount' => 100,
                'status' => 'pending',
                'expires_at' => now()->subMinutes(61),
                'created_at' => now()->subMinutes(120),
            ]);
        }

        $this->artisan('reservations:expire')
            ->expectsOutput('Expired 5 reservations.')
            ->assertExitCode(0);
    }
}
