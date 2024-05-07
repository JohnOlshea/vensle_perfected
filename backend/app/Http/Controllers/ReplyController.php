<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reply;
use App\Models\UserAlert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReplyController extends Controller
{
    public function store(Request $request, $messageId)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

    // Check if the message exists
    //$message = Message::find($messageId);

    //if (!$message) {
        //return response()->json(['error' => 'Message not found'], 404);
    //}

        $user = Auth::user();
        $reply = Reply::create([
            'message_id' => $messageId,
            'sender_id' => $user->id,
            'content' => $request->input('content'),
        ]);

	// Send alert to user
	UserAlert::create([
	    'user_id' => $user->id,
	    'title' => 'You hav a new message',
	    'message' => $reply->content,
	]);	


        return response()->json($reply, 201);
    }

    public function destroy($messageId, $replyId)
    {
        $reply = Reply::where('message_id', $messageId)->find($replyId);

        if (!$reply) {
            return response()->json(['error' => 'Reply not found'], 404);
        }

        // Check if the authenticated user is the sender of the reply
        $user = Auth::user();
        if ($user->id !== $reply->sender_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $reply->delete();
        return response()->json(['message' => 'Reply deleted successfully']);
    }	
}
