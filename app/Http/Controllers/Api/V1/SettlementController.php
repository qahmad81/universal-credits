<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ConfirmRequest;
use App\Http\Requests\Api\V1\CancelRequest;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class SettlementController extends Controller
{
    protected ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function confirm(ConfirmRequest $request): JsonResponse
    {
        $vendorToken = $request->attributes->get('vendor_token');

        try {
            $result = $this->reservationService->confirm(
                $vendorToken,
                (int) $request->reservation_id,
                (float) $request->actual_amount
            );

            return response()->json($result, 200);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (Throwable $e) {
            return response()->json(['message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    public function cancel(CancelRequest $request): JsonResponse
    {
        $vendorToken = $request->attributes->get('vendor_token');

        try {
            $result = $this->reservationService->cancel(
                $vendorToken,
                (int) $request->reservation_id
            );

            return response()->json($result, 200);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (Throwable $e) {
            return response()->json(['message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }
}
