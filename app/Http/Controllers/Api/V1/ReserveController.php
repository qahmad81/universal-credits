<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReserveRequest;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ReserveController extends Controller
{
    protected ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function __invoke(ReserveRequest $request): JsonResponse
    {
        $vendorToken = $request->attributes->get('vendor_token');

        // Check rate limit manually as requested
        $key = 'vendor_rate_limit:' . $vendorToken->id;
        $maxAttempts = $vendorToken->rate_limit_per_minute ?? 60;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json(['message' => 'Too many requests'], 429);
        }

        RateLimiter::hit($key, 60);

        try {
            $amount = (float) $request->amount;
            $result = $this->reservationService->reserve(
                $vendorToken,
                $request->client_token,
                $amount,
                $request->description
            );

            return response()->json($result, 200);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (Throwable $e) {
            return response()->json(['message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }
}
