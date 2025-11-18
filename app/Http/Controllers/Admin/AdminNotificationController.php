<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminNotificationController extends Controller
{
    public function markRead(Request $request): JsonResponse|Response
    {
        $admin = $request->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $admin->userNotifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'message' => 'Notifications marked as read.',
        ]);
    }

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



