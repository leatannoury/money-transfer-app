<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserNotificationController extends Controller
{
    public function markRead(Request $request): JsonResponse|Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user->userNotifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'message' => 'Notifications marked as read.',
        ]);
    }

    public function clear(Request $request): JsonResponse|Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user->userNotifications()->delete();

        return response()->json([
            'message' => 'Notifications cleared.',
            'remaining' => 0,
        ]);
    }
}

