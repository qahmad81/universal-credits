<?php

namespace Tests\Feature;

use App\Models\ClientBalance;
use App\Models\ClientToken;
use App\Models\PendingPayment;
use App\Models\User;
use App\Models\VendorToken;
use App\Services\UCMask;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettlementApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $clientToken;
    protected $vendorToken;
    protected $rawClientToken = 'client-secret-token';
    protected $rawVendorToken = 'vendor-secret-token';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        
        ClientBalance::create([
            'user_id' => $this->user->id,
            'final_balance' => 1000000,
            'pending_balance' => 1000000,
        ]);

        $this->clientToken = ClientToken::create([
            'user_id' => $this->user->id,
            'token_hash' => hash('sha256', $this->rawClientToken),
            'name' => 'Test Client Token',
            'limit_balance' => 500000,
            'final_balance' => 0,
            'pending_balance' => 0,
            'is_active' => true,
        ]);

        $this->vendorToken = VendorToken::create([
            'token_hash' => hash('sha256', $this->rawVendorToken),
            'name' => 'Test Vendor',
            'rate_limit_per_minute' => 60,
            'is_active' => true,
        ]);
    }

    protected function createReservation($amount = 100.00)
    {
        $dbAmount = UCMask::toDb($amount);
        
        $balance = ClientBalance::where('user_id', $this->user->id)->first();
        $balance->decrement('pending_balance', $dbAmount);
        $this->clientToken->increment('pending_balance', $dbAmount);

        return PendingPayment::create([
            'user_id' => $this->user->id,
            'vendor_token_id' => $this->vendorToken->id,
            'client_token_id' => $this->clientToken->id,
            'amount' => $dbAmount,
            'status' => 'pending',
            'expires_at' => Carbon::now()->addHour(),
            'created_at' => Carbon::now(),
        ]);
    }

    public function test_successful_confirm_full_amount()
    {
        $reservation = $this->createReservation(100.00);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/confirm', [
                'reservation_id' => $reservation->id,
                'actual_amount' => 100.00,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'amount_charged' => 100.00,
                'refunded' => 0,
                'warning' => null,
            ]);

        $this->assertDatabaseHas('pending_payments', [
            'id' => $reservation->id,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('client_balances', [
            'user_id' => $this->user->id,
            'final_balance' => 1000000 - UCMask::toDb(100.00),
            'pending_balance' => 1000000 - UCMask::toDb(100.00),
        ]);

        $this->assertDatabaseHas('client_tokens', [
            'id' => $this->clientToken->id,
            'final_balance' => UCMask::toDb(100.00),
            'pending_balance' => 0,
        ]);
    }

    public function test_confirm_partial_amount()
    {
        $reservation = $this->createReservation(100.00);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/confirm', [
                'reservation_id' => $reservation->id,
                'actual_amount' => 40.00,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'amount_charged' => 40.00,
                'refunded' => 60.00,
            ]);

        $this->assertDatabaseHas('client_balances', [
            'user_id' => $this->user->id,
            'final_balance' => 1000000 - UCMask::toDb(40.00),
            'pending_balance' => 1000000 - UCMask::toDb(40.00), // Original 1M - 100 reserved + 60 refund = 1M - 40
        ]);

        $this->assertDatabaseHas('client_tokens', [
            'id' => $this->clientToken->id,
            'final_balance' => UCMask::toDb(40.00),
            'pending_balance' => 0, // 100 - 100 refund/charged
        ]);
    }

    public function test_confirm_capped_amount()
    {
        $reservation = $this->createReservation(100.00);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/confirm', [
                'reservation_id' => $reservation->id,
                'actual_amount' => 150.00,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'amount_charged' => 100.00,
                'refunded' => 0,
            ])
            ->assertJsonPath('warning', 'Actual amount exceeded reserved amount; capped at 100');

        $this->assertDatabaseHas('client_balances', [
            'user_id' => $this->user->id,
            'final_balance' => 1000000 - UCMask::toDb(100.00),
        ]);
    }

    public function test_successful_cancel()
    {
        $reservation = $this->createReservation(100.00);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/cancel', [
                'reservation_id' => $reservation->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'amount_refunded' => 100.00,
            ]);

        $this->assertDatabaseHas('pending_payments', [
            'id' => $reservation->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('client_balances', [
            'user_id' => $this->user->id,
            'final_balance' => 1000000,
            'pending_balance' => 1000000,
        ]);

        $this->assertDatabaseHas('client_tokens', [
            'id' => $this->clientToken->id,
            'pending_balance' => 0,
        ]);
    }

    public function test_double_confirm_returns_409()
    {
        $reservation = $this->createReservation(100.00);
        $reservation->update(['status' => 'confirmed']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/confirm', [
                'reservation_id' => $reservation->id,
                'actual_amount' => 100.00,
            ]);

        $response->assertStatus(409);
    }

    public function test_double_cancel_returns_409()
    {
        $reservation = $this->createReservation(100.00);
        $reservation->update(['status' => 'cancelled']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/cancel', [
                'reservation_id' => $reservation->id,
            ]);

        $response->assertStatus(409);
    }

    public function test_confirm_expired_reservation_returns_410()
    {
        $reservation = $this->createReservation(100.00);
        $reservation->update(['expires_at' => Carbon::now()->subMinute()]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/confirm', [
                'reservation_id' => $reservation->id,
                'actual_amount' => 100.00,
            ]);

        $response->assertStatus(410);
    }

    public function test_cancel_expired_reservation_returns_410()
    {
        $reservation = $this->createReservation(100.00);
        $reservation->update(['expires_at' => Carbon::now()->subMinute()]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/cancel', [
                'reservation_id' => $reservation->id,
            ]);

        $response->assertStatus(410);
    }

    public function test_invalid_reservation_id_returns_404()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/confirm', [
                'reservation_id' => 99999,
                'actual_amount' => 10.00,
            ]);

        $response->assertStatus(404);
    }

    public function test_confirm_wrong_vendor_returns_404()
    {
        $otherVendor = VendorToken::create([
            'token_hash' => hash('sha256', 'other-token'),
            'name' => 'Other Vendor',
            'is_active' => true,
        ]);

        $reservation = $this->createReservation(100.00);

        $response = $this->withHeader('Authorization', 'Bearer other-token')
            ->postJson('/api/v1/confirm', [
                'reservation_id' => $reservation->id,
                'actual_amount' => 100.00,
            ]);

        $response->assertStatus(404);
    }
}
