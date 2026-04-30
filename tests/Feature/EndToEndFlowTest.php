<?php

namespace Tests\Feature;

use App\Models\ClientBalance;
use App\Models\ClientToken;
use App\Models\User;
use App\Models\VendorToken;
use App\Services\UCMask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EndToEndFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_reservation_and_confirmation_flow(): void
    {
        // 1. Create a user (client)
        $client = User::factory()->create([
            'is_admin' => false,
        ]);

        // 2. Create client balance with 100000 DB units (1000.00 UC)
        $balance = ClientBalance::create([
            'user_id' => $client->id,
            'final_balance' => 100000,
            'pending_balance' => 100000,
        ]);

        // 3. Create a client token with limit 50000 (500.00 UC)
        $clientTokenStr = 'ct_' . Str::random(40);
        $clientToken = ClientToken::create([
            'user_id' => $client->id,
            'name' => 'Test Client Token',
            'token_hash' => hash('sha256', $clientTokenStr),
            'limit_balance' => 50000,
            'final_balance' => 0,
            'pending_balance' => 0,
            'is_active' => true,
        ]);

        // 4. Create a vendor token
        $vendorTokenStr = 'vt_' . Str::random(40);
        $vendorToken = VendorToken::create([
            'name' => 'Test Vendor Token',
            'token_hash' => hash('sha256', $vendorTokenStr),
            'rate_limit_per_minute' => 60,
            'is_active' => true,
        ]);

        // 5. Reserve 100.00 UC via API -> expect success with reservation_id
        $reserveResponse = $this->withHeader('Authorization', 'Bearer ' . $vendorTokenStr)
            ->postJson('/api/v1/reserve', [
                'client_token' => $clientTokenStr,
                'amount' => 100.00,
                'description' => 'Test Reservation',
            ]);

        $reserveResponse->assertStatus(200)
            ->assertJsonStructure(['reservation_id', 'amount_reserved', 'expires_at']);

        $reservationId = $reserveResponse->json('reservation_id');

        // 6. Confirm with actual_amount 80.00 UC -> expect success, verify refund of 20.00
        $confirmResponse = $this->withHeader('Authorization', 'Bearer ' . $vendorTokenStr)
            ->postJson('/api/v1/confirm', [
                'reservation_id' => $reservationId,
                'actual_amount' => 80.00,
            ]);

        $confirmResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'amount_charged' => 80.00,
                'refunded' => 20.00,
            ]);

        // 7. Verify client final_balance decreased by 80.00 UC worth (8000 DB units)
        $balance->refresh();
        $this->assertEquals(92000, $balance->final_balance);

        // 8. Verify client pending_balance = final_balance (no pending reservations)
        $this->assertEquals($balance->final_balance, $balance->pending_balance);

        // 9. Verify client_token final_balance increased by 80.00 UC worth
        $clientToken->refresh();
        $this->assertEquals(8000, $clientToken->final_balance);
        $this->assertEquals(0, $clientToken->pending_balance);

        // 10. Reserve again with amount exceeding remaining balance -> expect 402
        // Client balance is 92000 (920 UC). Client token limit is 50000 (500 UC).
        // Already spent 8000 (80 UC). Remaining limit is 42000 (420 UC).
        // Trying to reserve 500 UC (50000) should fail because 0 + 10000 (pending from first) + 50000 > 50000? 
        // No, pending was cleared on confirm.
        // Let's try 501 UC.
        $overLimitResponse = $this->withHeader('Authorization', 'Bearer ' . $vendorTokenStr)
            ->postJson('/api/v1/reserve', [
                'client_token' => $clientTokenStr,
                'amount' => 501.00,
                'description' => 'Over Limit Reservation',
            ]);

        $overLimitResponse->assertStatus(402);
        
        // Ensure token was deactivated
        $clientToken->refresh();
        $this->assertFalse($clientToken->is_active);

        // 11. Verify transaction records exist for all operations
        $this->assertDatabaseHas('transactions', [
            'type' => 'reserve',
            'amount' => 10000,
            'pending_payment_id' => $reservationId,
        ]);

        $this->assertDatabaseHas('transactions', [
            'type' => 'confirm',
            'amount' => 8000,
            'pending_payment_id' => $reservationId,
        ]);
    }

    public function test_cancel_flow(): void
    {
        // Setup
        $client = User::factory()->create();
        $balance = ClientBalance::create([
            'user_id' => $client->id,
            'final_balance' => 100000,
            'pending_balance' => 100000,
        ]);
        $clientTokenStr = 'ct_' . Str::random(40);
        $clientToken = ClientToken::create([
            'user_id' => $client->id,
            'name' => 'Test Client Token',
            'token_hash' => hash('sha256', $clientTokenStr),
            'limit_balance' => 50000,
            'final_balance' => 0,
            'pending_balance' => 0,
            'is_active' => true,
        ]);
        $vendorTokenStr = 'vt_' . Str::random(40);
        $vendorToken = VendorToken::create([
            'name' => 'Test Vendor Token',
            'token_hash' => hash('sha256', $vendorTokenStr),
            'rate_limit_per_minute' => 60,
            'is_active' => true,
        ]);

        // 1. Reserve 50.00 UC
        $reserveResponse = $this->withHeader('Authorization', 'Bearer ' . $vendorTokenStr)
            ->postJson('/api/v1/reserve', [
                'client_token' => $clientTokenStr,
                'amount' => 50.00,
            ]);
        
        $reservationId = $reserveResponse->json('reservation_id');

        // 2. Cancel it
        $cancelResponse = $this->withHeader('Authorization', 'Bearer ' . $vendorTokenStr)
            ->postJson('/api/v1/cancel', [
                'reservation_id' => $reservationId,
            ]);

        $cancelResponse->assertStatus(200)
            ->assertJson(['success' => true, 'amount_refunded' => 50.00]);

        // 3. Verify all balances returned to pre-reservation state
        $balance->refresh();
        $this->assertEquals(100000, $balance->final_balance);
        $this->assertEquals(100000, $balance->pending_balance);

        $clientToken->refresh();
        $this->assertEquals(0, $clientToken->final_balance);
        $this->assertEquals(0, $clientToken->pending_balance);

        $this->assertDatabaseHas('transactions', [
            'type' => 'cancel',
            'amount' => 5000,
            'pending_payment_id' => $reservationId,
        ]);
    }
}
