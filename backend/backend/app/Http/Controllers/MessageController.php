<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @group Message Management
 *
 * APIs to manage message
 */
class MessageController extends Controller
{
    //TODO: Authorization, only admin can retrieve all messages
    public function index()
    {
        // Retrieve all messages for the authenticated user
        // $user = Auth::user();
        //$messages = Message::where('sender_id', $user->id)
        //->orWhere('receiver_id', $user->id)->get();

        $messages = Message::all();
        return response()->json($messages);
    }

public function getChatsWithUser(string $userId)
{
    $authUserId = Auth::id();

    // Get messages between the authenticated user and the specified userId,
    $messages = Message::where(function($query) use ($authUserId, $userId) {
        $query->where('sender_id', $authUserId)
              ->where('receiver_id', $userId);
    })->orWhere(function($query) use ($authUserId, $userId) {
        $query->where('sender_id', $userId)
              ->where('receiver_id', $authUserId);
    })->with(['sender', 'receiver'])
      ->orderBy('created_at', 'asc')
      ->get();

    return response()->json(['chats' => $messages]);	
}

    
public function getLastChat()
{
    $userId = Auth::id();

    // Get distinct chat partners and their messages, ordered by created_at desc
    $messages = Message::where('sender_id', $userId)
        ->orWhere('receiver_id', $userId)
        ->with(['sender', 'receiver'])
        ->orderBy('created_at', 'desc')
        ->get();

    // Group messages by chat partners
    $chatPartners = $messages->groupBy(function ($message) use ($userId) {
        return $message->sender_id == $userId ? $message->receiver_id : $message->sender_id;
    });

    // Retrieve the last message and count unread messages for each chat partner
    $chats = $chatPartners->map(function ($messages) use ($userId) {
        $lastMessage = $messages->first();
        $unreadCount = $messages->where('receiver_id', $userId)->where('is_read', false)->count();
        
        return [
            'last_message' => $lastMessage->toArray(),
            'unread_count' => $unreadCount
        ];
    })->values()->all();

    return response()->json(['chats' => $chats]);
}

    public function getInboxMessages(Request $request)
    {
        try {

                $request->validate(
                    [
                    'per_page' => 'sometimes|integer|nullable',
                    ]
                );

            $perPage = $request->input('per_page');

            $user = Auth::user();

            if (!$perPage) {
                $perPage = 10;
            }
            $inboxMessages = Message::with(['sender', 'receiver', 'product.displayImage', 'latestReply'])
                ->where('receiver_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json($inboxMessages);
        } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function getSentMessages(Request $request)
    {
                $request->validate(
                    [
                    'per_page' => 'sometimes|integer|nullable',
                    ]
                );

        $perPage = $request->input('per_page');

            $user = Auth::user();

        if (!$perPage) {
            $perPage = 10;
        }

        $sentMessages = Message::with(['sender', 'receiver', 'product.displayImage', 'latestReply'])
            ->where('sender_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($sentMessages);
    }

    public function show($id)
    {
        $user = Auth::user();

        $message = Message::with(
            ['sender', 'receiver', 'product.displayImage', 'replies' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }]
        )
        ->find($id);

        // Check if the message belongs to the user
	//if (!$message || ($message->sender_id !== $user->id && $message->receiver_id !== $user->id)) {
	    //return response()->json(['error' => 'Unauthorized'], 403);
	//}

        return response()->json($message);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(), [
            'receiver_id' => 'required|integer|exists:users,id',
            'content' => 'required',
            'product_id' => 'nullable|exists:products,id',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = Auth::user();
        $message = Message::create(
            [
            'sender_id' => $user->id,
            'receiver_id' => $request->input('receiver_id') * 1,
            'content' => $request->input('content'),
            'product_id' => $request->input('product_id'),
            'read' => false,
            ]
        );
	//temp solution
        $new_message = Message::with(['sender', 'receiver'])->find($message->id);
        return response()->json($new_message, 201);
    }

    public function destroy($id)
    {
        $message = Message::find($id);

        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        // Check if the authenticated user is the sender of the message
        $user = Auth::user();
        if ($user->id !== $message->sender_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->delete();
        return response()->json(['message' => 'Message deleted successfully']);
    }
}
