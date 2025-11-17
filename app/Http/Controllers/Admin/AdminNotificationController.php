<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminNotificationController extends Controller
{
    public function clear(Request $request): JsonResponse|Response
    {
        $admin = $request->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $admin->userNotifications()->delete();

        return response()->json([
            'message' => 'Notifications cleared.',
            'remaining' => 0,
        ]);
    }
}



