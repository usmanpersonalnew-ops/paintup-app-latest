<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PhonePeCallbackController extends Controller
{
    /**
     * PhonePe server-to-server callback (optional webhook).
     * Payment completion is handled when the user hits the redirectUrl.
     */
    public function __invoke(Request $request): JsonResponse
    {
        Log::info('PhonePe API callback received', [
            'keys' => array_keys($request->all()),
        ]);

        return response()->json(['success' => true]);
    }
}
