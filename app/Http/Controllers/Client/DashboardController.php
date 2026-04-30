<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientToken;
use App\Services\UCMask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $balance = $user->clientBalance;
        
        $displayBalance = UCMask::toDisplay(UCMask::fromDb($balance->final_balance ?? 0));
        
        $tokens = $user->clientTokens()->latest()->get()->map(function ($token) {
            $token->display_limit = UCMask::toDisplay(UCMask::fromDb($token->limit_balance));
            $token->display_used = UCMask::toDisplay(UCMask::fromDb($token->final_balance));
            $token->masked_token = substr($token->token_hash, 0, 8) . '***'; // Actually the token is hashed, the user won't see the raw token again. The instruction says 1st 8 chars of masked token.
            return $token;
        });

        $transactions = $user->transactions()->latest()->take(20)->get()->map(function ($tx) {
            $tx->display_amount = UCMask::toDisplay(UCMask::fromDb($tx->amount));
            return $tx;
        });

        return view('client.dashboard', compact('displayBalance', 'tokens', 'transactions'));
    }

    public function createToken(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'limit' => 'required|integer|min:0',
        ]);

        $rawToken = Str::random(64);
        $tokenHash = hash('sha256', $rawToken);

        $token = $request->user()->clientTokens()->create([
            'name' => $request->name,
            'token_hash' => $tokenHash,
            'limit_balance' => UCMask::toDb((float)$request->limit),
            'final_balance' => 0,
            'pending_balance' => 0,
            'is_active' => true,
        ]);

        return back()->with('success', 'Token created successfully. Raw token (show once): ' . $rawToken);
    }

    public function deactivateToken(Request $request, $id)
    {
        $token = $request->user()->clientTokens()->findOrFail($id);
        $token->update(['is_active' => false]);

        return back()->with('success', 'Token deactivated.');
    }
}
