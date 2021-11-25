<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Usermessage;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ChatboxController extends Controller
{
    public function send_message(Request $request)
    {
        $decoded = $request->decoded;
        $chat = new Chat();
        $usermessage = new Usermessage();
        $chat->message = $request->message;
        $chat->user()->associate($decoded->data->id);
        $chat->save();
        $usermessage->user()->associate($decoded->data->id);
        $usermessage->chat()->associate($chat->id);
        $usermessage->save();
        $m = [
            "status"=>"success",
            "message"=>"message Sent"
        ];
        return response()->json($m);
    }
}
