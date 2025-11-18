<?php

namespace App\Http\Controllers\User;

use App\Models\ChatRoom;
use App\Models\ChatRoomMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SupportChatController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $admin = User::role('Admin')->first();
        // Check if an open chat exists
        $chatRoom = ChatRoom::where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        // If none -> create new one
       if (!$admin) {
    abort(500, 'No admin available to handle the chat.');
}

if (!$chatRoom) {
    $chatRoom = ChatRoom::create([
        'user_id' => $user->id,
        'admin_id' => $admin->id,
        'status' => 'open',
    ]);
}

        // Mark admin messages as read
        ChatRoomMessage::where('chat_room_id', $chatRoom->id)
            ->where('sender_type', 'admin')
            ->update(['read_status' => true]);

        return view('user.chat.index', [
            'chatRoom' => $chatRoom,
            'messages' => $chatRoom->messages()->orderBy('created_at')->get()
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $chatRoom = ChatRoom::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        ChatRoomMessage::create([
            'chat_room_id' => $chatRoom->id,
            'sender_id' => Auth::id(),
            'sender_type' => 'user',
            'message' => $request->message,
        ]);

        return back();
    }
}
