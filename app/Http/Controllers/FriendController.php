<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Friend;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use Exception;

class FriendController extends Controller
{
    public function add_friend(Request $request)
    {
        try
        {
        $jwt = $request->bearerToken();
        $decoded = (new JwtController)->jwt_decode($jwt);
        if((Friend::where([["email",$request->email],["user_id",$decoded->data->id]])->exists()))
        {
            $m = [
                "status"=>"Failed",
                "message"=>"This user is already your friend"
            ];
            return response()->error($m,403);
        }
        else
        {
            $user = User::where('email',$request->email)->first();
            $friend = new Friend();
            $friend1 = new Friend();
            $f = $user->id;
            $friend->email = $user->email;
            $friend->name = $user->name;
            $friend->user()->associate($decoded->data->id);
            $friend->save();
            if(!(Friend::where('email',$decoded->data->email)->exists()))
            {
            $user = User::where('email',$decoded->data->email)->first();
            $friend1->email = $user->email;
            $friend1->name = $user->name;
            $friend1->user()->associate($f);
            $friend1->save();

            }
            $m = [
                "status"=>"success",
                "message"=>"Friend Added"
            ];
            return response()->success($m,200);
        }
            }
            catch(Exception $e)
            {
                return response()->error($e->getMessage(),400);
            }
    }

    public function remove_friend(Request $request)
    {
        try
        {
        $jwt = $request->bearerToken();
        $decoded = (new JwtController)->jwt_decode($jwt);
        if(!Friend::where([["email",$request->email],["user_id",$decoded->data->id]])->exists())
        {
            $m = [
                "status"=>"Failed",
                "message"=>"No such User exists"
            ];
            return response()->error($m,404);
        }
        else{

            $friend = Friend::where([["email",$request->email],["user_id",$decoded->data->id]])->first();
            $friend_data = User::where("email",$request->email)->first();
            $friend_id = $friend_data->id;
            $friend->delete();
            $friend = Friend::where([["email",$decoded->data->email],["user_id",$friend_id]]);
            $friend->delete();
            $m = [
                "status"=>"Success",
                "message"=>"Friend Deleted"
            ];
            return response()->success($m,200);
        }
        }
        catch(Exception $e)
        {
            return response()->error($e->getMessage(),400);
        }
        }
}
