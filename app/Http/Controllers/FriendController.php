<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Friend;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use MongoDB\Client as mongodb;
use Exception;
class FriendController extends Controller
{
    public function add_friend(Request $request)
    {
        $jwt = $request->bearerToken();
        $decoded = (new JwtController)->jwt_decode($jwt);
        $user = (new mongodb)->laravel_project->users->findOne(['_id'=>new \MongoDB\BSON\ObjectId($decoded->data->id)]);
        foreach($user->friend as $friend)
        {
            if($friend->friend_id == $request->id)
            {
                $m = [
                    "status"=> "failed",
                    "message"=>"This preson is already your friend"
                ];
                return response()->error($m,400);
            }

        }
        (new mongodb)->laravel_project->users->updateOne(["_id"=>new \MongoDB\BSON\ObjectId($decoded->data->id)],
        ['$push'=>["friend"=>['friend_id'=>$request->id]]]);

        (new mongodb)->laravel_project->users->updateOne(["_id"=>new \MongoDB\BSON\ObjectId($request->id)],
        ['$push'=>["friend"=>['friend_id'=>$decoded->data->id]]]);
            $m = [
                "status"=>"Success",
                "message"=>"Friend Added"
            ];
            return response()->success($m,200);

    }

    public function remove_friend(Request $request)
    {
        try{
        $jwt = $request->bearerToken();
        $decoded = (new JwtController)->jwt_decode($jwt);
        $user = (new mongodb)->laravel_project->users->findOne(['_id'=>new \MongoDB\BSON\ObjectId($decoded->data->id),'friend.friend_id'=>$request->id]
    );
    //dd($user);
        if(!isset($user))
           {
            throw new Exception("No such friend Exist");
           }
        (new mongodb)->laravel_project->users->updateOne(['_id'=>new \MongoDB\BSON\ObjectId($request->id)],
        ['$pull'=>['friend'=>['friend_id'=>$decoded->data->id]]]
    );

        (new mongodb)->laravel_project->users->updateOne(['_id'=>new \MongoDB\BSON\ObjectId($decoded->data->id)],
        ['$pull'=>['friend'=>['friend_id'=>$request->id]]]
    );

            $m = [
                "status"=>"Success",
                "message"=>"Friend Deleted"
            ];

            return response()->success($m,201);
        }
        catch(Exception $e)
        {
            return response()->error($e->getMessage(),400);
        }

    }
}
