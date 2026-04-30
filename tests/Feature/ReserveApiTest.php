<?php

namespace Tests\Feature;

use App\Models\ClientBalance;
use App\Models\ClientToken;
use App\Models\User;
use App\Models\VendorToken;
use App\Services\UCMask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReserveApiTest extends TestCase
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
        
        // 10,000.00 UC = 1,000,000 DB units
        ClientBalance::create([
            'user_id' => $this->user->id,
            'final_balance' => 1000000,
            'pending_balance' => 1000000,
        ]);

        // 5,000.00 UC = 500,000 DB units
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

    public function test_successful_reservation()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/reserve', [
                'client_token' => $this->rawClientToken,
                'amount' => 100.50, // 100.50 UC
                'description' => 'Test Reservation',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'reservation_id',
                'amount_reserved',
                'expires_at',
            ]);

        $this->assertDatabaseHas('pending_payments', [
            'amount' => UCMask::toDb(100.50),
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('client_balances', [
            'user_id' => $this->user->id,
            'pending_balance' => 1000000 - UCMask::toDb(100.50),
        ]);

        $this->assertDatabaseHas('client_tokens', [
            'id' => $this->clientToken->id,
            'pending_balance' => UCMask::toDb(100.50),
        ]);
    }

    public function test_insufficient_balance()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/reserve', [
                'client_token' => $this->rawClientToken,
                'amount' => 15000.00, // Exceeds 10,000.00 balance
            ]);

        $response->assertStatus(402)
            ->assertJson(['message' => 'Insufficient balance']);
    }

    public function test_token_limit_exceeded()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/reserve', [
                'client_token' => $this->rawClientToken,
                'amount' => 6000.00, // Exceeds 5,000.00 limit
            ]);

        $response->assertStatus(402)
            ->assertJson(['message' => 'Token limit reached']);

        $this->assertDatabaseHas('client_tokens', [
            'id' => $this->clientToken->id,
            'is_active' => false,
        ]);
    }

    public function test_invalid_vendor_token()
    {
        $response = $this->withHeader('Authorization', 'Bearer wrong-token')
            ->postJson('/api/v1/reserve', [
                'client_token' => $this->rawClientToken,
                'amount' => 10.00,
            ]);

        $response->assertStatus(401);
    }

    public function test_invalid_client_token()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/reserve', [
                'client_token' => 'wrong-client-token',
                'amount' => 10.00,
            ]);

        $response->assertStatus(404);
    }

    public function test_rate_limiting()
    {
        $this->vendorToken->update(['rate_limit_per_minute' => 1]);

        // First request
        $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/reserve', [
                'client_token' => $this->rawClientToken,
                'amount' => 10.00,
            ])->assertStatus(200);

        // Second request
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/reserve', [
                'client_token' => $this->rawClientToken,
                'amount' => 10.00,
            ]);

        $response->assertStatus(429);
    }

    public function test_concurrent_reservations_dont_double_spend()
    {
        $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/reserve', [
                'client_token' => $this->rawClientToken,
                'amount' => 5000.00,
            ])->assertStatus(200);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->rawVendorToken)
            ->postJson('/api/v1/reserve', [
                'client_token' => $this->rawClientToken,
                'amount' => 1.00,
            ]);

        // Second one should fail because limit is 5000.00
        $response->assertStatus(402);
    }
}
