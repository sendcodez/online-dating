<?php

namespace App\Http\Controllers;

use App\Events\ChatPairing;
use App\Models\ChatUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RandomChatController extends Controller
{
    public function index()
    {
        return view('random-chat');
    }

    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'userId' => 'required|string',
            'country' => 'nullable|string',
            'city' => 'nullable|string',
            'interests' => 'nullable|array',
            'interests.*' => 'string'
        ]);

        ChatUser::updateOrCreate(
            ['user_id' => $validated['userId']],
            [
                'country' => $validated['country'] ?? null,
                'city' => $validated['city'] ?? null,
                'interests' => $validated['interests'] ?? [],
                'last_active' => now()
            ]
        );

        return response()->json(['status' => 'updated']);
    }

    public function findPartner(Request $request)
{
    $userId = $request->input('userId');

    // Update last active
    $currentUser = ChatUser::where('user_id', $userId)->first();
    if ($currentUser) {
        $currentUser->update(['last_active' => now()]);
    }

    // Check if already in a room (prevent duplicate matching)
    $existingRoom = Cache::get("user_room_{$userId}");
    if ($existingRoom) {
        return response()->json([
            'status' => 'paired',
            'roomId' => $existingRoom['roomId'],
            'partnerId' => $existingRoom['partnerId'],
            'matchInfo' => $this->getMatchInfo($userId, $existingRoom['partnerId'])
        ]);
    }

    // Get waiting users from cache
    $waitingUsers = Cache::get('waiting_users', []);

    // Remove current user from waiting list if exists
    $waitingUsers = array_filter($waitingUsers, fn($id) => $id !== $userId);

    // Remove users who are already in rooms
    $waitingUsers = array_filter($waitingUsers, function($waitingUserId) {
        return !Cache::has("user_room_{$waitingUserId}");
    });

    if (count($waitingUsers) > 0) {
        // Find best match based on location and interests
        $bestMatch = $this->findBestMatch($userId, $waitingUsers);

        if ($bestMatch) {
            $partnerId = $bestMatch;
            $roomId = Str::uuid();

            // Remove matched user from waiting list
            $waitingUsers = array_filter($waitingUsers, fn($id) => $id !== $partnerId);

            // Store room mapping for both users
            $roomData1 = ['roomId' => $roomId, 'partnerId' => $partnerId];
            $roomData2 = ['roomId' => $roomId, 'partnerId' => $userId];

            Cache::put("user_room_{$userId}", $roomData1, 3600);
            Cache::put("user_room_{$partnerId}", $roomData2, 3600);

            // Update waiting list
            Cache::put('waiting_users', array_values($waitingUsers), 3600);

            // Get match info
            $matchInfo = $this->getMatchInfo($userId, $partnerId);

            // Notify both users with match info
            broadcast(new ChatPairing($roomId, $userId, 'paired', json_encode($matchInfo)));
            broadcast(new ChatPairing($roomId, $partnerId, 'paired', json_encode($matchInfo)));

            return response()->json([
                'status' => 'paired',
                'roomId' => $roomId,
                'partnerId' => $partnerId,
                'matchInfo' => $matchInfo
            ]);
        }
    }

    // Add to waiting list (avoid duplicates)
    if (!in_array($userId, $waitingUsers)) {
        $waitingUsers[] = $userId;
        Cache::put('waiting_users', array_values($waitingUsers), 3600);
    }

    return response()->json([
        'status' => 'waiting',
        'waitingCount' => count($waitingUsers)
    ]);
}

    private function findBestMatch($userId, $waitingUsers)
    {
        $currentUser = ChatUser::where('user_id', $userId)->first();

        if (!$currentUser || empty($waitingUsers)) {
            return $waitingUsers[0] ?? null;
        }

        $scores = [];

        foreach ($waitingUsers as $waitingUserId) {
            $waitingUser = ChatUser::where('user_id', $waitingUserId)->first();

            if (!$waitingUser) {
                $scores[$waitingUserId] = 0;
                continue;
            }

            $score = 0;

            // Location matching (30 points max)
            if ($currentUser->country && $waitingUser->country) {
                if ($currentUser->country === $waitingUser->country) {
                    $score += 20;

                    if ($currentUser->city && $waitingUser->city &&
                        $currentUser->city === $waitingUser->city) {
                        $score += 10;
                    }
                }
            }

            // Interest matching (70 points max)
            $currentInterests = $currentUser->interests ?? [];
            $waitingInterests = $waitingUser->interests ?? [];

            if (!empty($currentInterests) && !empty($waitingInterests)) {
                $commonInterests = array_intersect($currentInterests, $waitingInterests);
                $interestScore = count($commonInterests) * 14; // 14 points per common interest
                $score += min($interestScore, 70);
            }

            $scores[$waitingUserId] = $score;
        }

        // Sort by score (highest first)
        arsort($scores);

        // Return best match (or first if no scoring difference)
        return array_key_first($scores);
    }

    private function getMatchInfo($userId1, $userId2)
    {
        $user1 = ChatUser::where('user_id', $userId1)->first();
        $user2 = ChatUser::where('user_id', $userId2)->first();

        $info = [
            'commonInterests' => [],
            'sameCountry' => false,
            'sameCity' => false
        ];

        if ($user1 && $user2) {
            // Check location
            if ($user1->country && $user2->country && $user1->country === $user2->country) {
                $info['sameCountry'] = true;
                $info['country'] = $user1->country;
            }

            if ($user1->city && $user2->city && $user1->city === $user2->city) {
                $info['sameCity'] = true;
                $info['city'] = $user1->city;
            }

            // Check interests
            $interests1 = $user1->interests ?? [];
            $interests2 = $user2->interests ?? [];

            if (!empty($interests1) && !empty($interests2)) {
                $info['commonInterests'] = array_values(array_intersect($interests1, $interests2));
            }
        }

        return $info;
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'userId' => 'required|string',
            'message' => 'required|string|max:1000',
        ]);

        $userRoom = Cache::get("user_room_{$validated['userId']}");

        if (!$userRoom) {
            return response()->json(['error' => 'No active chat'], 404);
        }

        broadcast(new ChatPairing(
            $userRoom['roomId'],
            $validated['userId'],
            'message',
            $validated['message']
        ));

        return response()->json(['status' => 'sent']);
    }

    public function typing(Request $request)
    {
        $userId = $request->input('userId');
        $isTyping = $request->input('isTyping');

        $userRoom = Cache::get("user_room_{$userId}");

        if ($userRoom) {
            broadcast(new ChatPairing(
                $userRoom['roomId'],
                $userId,
                'typing',
                $isTyping ? 'true' : 'false'
            ));
        }

        return response()->json(['status' => 'ok']);
    }

    public function disconnect(Request $request)
    {
        $userId = $request->input('userId');

        // Remove from waiting list
        $waitingUsers = Cache::get('waiting_users', []);
        $waitingUsers = array_filter($waitingUsers, fn($id) => $id !== $userId);
        Cache::put('waiting_users', $waitingUsers, 3600);

        // Notify partner if in a room
        $userRoom = Cache::get("user_room_{$userId}");
        if ($userRoom) {
            broadcast(new ChatPairing(
                $userRoom['roomId'],
                $userId,
                'disconnected'
            ));

            Cache::forget("user_room_{$userId}");
            Cache::forget("user_room_{$userRoom['partnerId']}");
        }

        return response()->json(['status' => 'disconnected']);
    }
}
