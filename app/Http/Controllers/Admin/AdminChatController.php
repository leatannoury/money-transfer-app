<?php

namespace App\Http\Controllers\Admin;

use App\Models\ChatRoom;
use App\Models\ChatRoomMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AdminChatController extends Controller
{
    public function index()
    {
        $chatRooms  = ChatRoom::
            with('user')
            ->get();

        return view('admin.chat.index', compact('chatRooms'));
    }

    public function show(ChatRoom $chatRoom)
    {
        // Mark user messages as read
        ChatRoomMessage::where('chat_room_id', $chatRoom->id)
            ->where('sender_type', 'user')
            ->update(['read_status' => true]);

        return view('admin.chat.show', [
            'chatRoom' => $chatRoom,
            'messages' => $chatRoom->messages()->orderBy('created_at')->get()
        ]);
    }

    public function sendMessage(Request $request, ChatRoom $chatRoom)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        ChatRoomMessage::create([
            'chat_room_id' => $chatRoom->id,
            'sender_id' => Auth::id(), // admin
            'sender_type' => 'admin',
            'message' => $request->message,
        ]);

        return back();
    }

    public function close(ChatRoom $chatRoom)
    {
        $chatRoom->update(['status' => 'closed']);

        return redirect()->route('admin.chat.index')->with('success', 'Chat closed successfully.');
    }
}
