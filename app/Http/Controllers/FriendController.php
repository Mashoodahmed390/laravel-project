<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Friend;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;

class FriendController extends Controller
{
    public function add_friend(Request $request)
    {
        $jwt = $request->bearerToken();
        $key = "example_key";
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

        if((Friend::where([["email",$request->email],["user_id",$decoded->data->id]])->exists()))
        {
            $m = [
                "status"=>"Failed",
                "message"=>"This user is already your friend"
            ];

            return response()->json($m);
        }
        else
        {
            $user = User::where('email',$request->email)->first();
            $friend = new Friend();
            $friend1 = new Friend();

            //This will create a error
            // $f = $user->id;
            // $friend->email = $user->email;
            // $friend->name = $user->name;
            // $friend->user()->associate($decoded->data->id);
            // $friend->save();
            // //Making reverse friend
            // $user = User::where('email',$decoded->data->email)->first();
            // $friend->email = $user->email;
            // $friend->name = $user->name;
            // $friend->user()->associate($f);
            // $friend->save();
            //error Ends here

            $f = $user->id;
            $friend->email = $user->email;
            $friend->name = $user->name;
            $friend->user()->associate($decoded->data->id);
            $friend->save();
            //Making reverse friend

            if(!(Friend::where('email',$decoded->data->email)->exists()))
            {

            $user = User::where('email',$decoded->data->email)->first();
            $friend1->email = $user->email;
            $friend1->name = $user->name;
            $friend1->user()->associate($f);
            $friend1->save();

            }

            $m = [
                "status"=>"Susscess",
                "message"=>"Friend Added"
            ];
            return response()->json($m);
            }
    }

    public function remove_friend(Request $request)
    {
        $jwt = $request->bearerToken();
        $key = "example_key";
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

        if(!Friend::where([["email",$request->email],["user_id",$decoded->data->id]])->exists())
        {
            $m = [
                "status"=>"Failed",
                "message"=>"No such User exists"
            ];

            return response()->json($m);
        }
        else{

            $friend = Friend::where([["email",$request->email],["user_id",$decoded->data->id]]);
            $friend->delete();

            $m = [
                "status"=>"Success",
                "message"=>"Friend Deleted"
            ];

            return response()->json($m);
        }
    }
}
