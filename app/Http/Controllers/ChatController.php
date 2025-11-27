<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500',
            'username' => 'required|string|max:50',
        ]);

        broadcast(new MessageSent(
            $validated['message'],
            $validated['username']
        ));

        return response()->json([
            'status' => 'Message sent!',
            'data' => $validated
        ]);
    }
}
