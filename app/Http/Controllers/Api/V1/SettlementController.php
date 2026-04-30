<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SettlementController extends Controller
{
    protected ReservationService $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function confirm(Request $request): JsonResponse
    {
        $vendorToken = $request->attributes->get('vendor_token');
        
        $request->validate([
            'reservation_id' => 'required|integer',
            'actual_amount' => 'required|numeric|min:0',
        ]);

        try {
            $result = $this->reservationService->confirm(
                $vendorToken,
                (int) $request->reservation_id,
                (float) $request->actual_amount
            );

            return response()->json($result, 200);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    public function cancel(Request $request): JsonResponse
    {
        $vendorToken = $request->attributes->get('vendor_token');

        $request->validate([
            'reservation_id' => 'required|integer',
        ]);

        try {
            $result = $this->reservationService->cancel(
                $vendorToken,
                (int) $request->reservation_id
            );

            return response()->json($result, 200);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }
}
