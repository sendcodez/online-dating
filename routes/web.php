<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Events\TestEvent;
use App\Http\Controllers\RandomChatController;

Route::get('/chat', [ChatController::class, 'index']);
Route::post('/chat/send', [ChatController::class, 'sendMessage']);
Route::get('/test-reverb', function () {
    return view('test-reverb');
});

Route::post('/test-broadcast', function () {
    broadcast(new TestEvent('Hello from Reverb!'));
    return response()->json(['status' => 'Broadcast sent!']);
});

Route::get('/test-reverb-cdn', function () {
    return view('test-reverb-cdn');
});

Route::get('/test-send', function () {
    broadcast(new App\Events\MessageSent('Test message', 'System'));
    return 'Message broadcast! Check your chat window.';
});


Route::get('/random-chat', [RandomChatController::class, 'index']);
Route::post('/random-chat/preferences', [RandomChatController::class, 'updatePreferences']);
Route::post('/random-chat/find', [RandomChatController::class, 'findPartner']);
Route::post('/random-chat/message', [RandomChatController::class, 'sendMessage']);
Route::post('/random-chat/typing', [RandomChatController::class, 'typing']);
Route::post('/random-chat/disconnect', [RandomChatController::class, 'disconnect']);

Route::get('/random-chat/debug', function () {
    $waitingUsers = Cache::get('waiting_users', []);
    $rooms = [];

    foreach ($waitingUsers as $userId) {
        $room = Cache::get("user_room_{$userId}");
        $rooms[$userId] = $room;
    }

    return response()->json([
        'waiting_users' => $waitingUsers,
        'user_rooms' => $rooms
    ]);
});
