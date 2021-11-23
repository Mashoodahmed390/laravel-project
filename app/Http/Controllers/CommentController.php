<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Exception;
use MongoDB\Client as mongodb;
class CommentController extends Controller
{
    public function comment(Request $request)
    {
        try
       {
        $jwt = $request->bearerToken();
        $decoded = (new JwtController)->jwt_decode($jwt);
        $user = $decoded->data->id;
        $post = (new mongodb)->laravel_project->posts->updateOne(["_id"=>new \MongoDB\BSON\ObjectId($request->id)],
        ['$push'=>["comment"=>["_id"=>new \MongoDB\BSON\ObjectId(),'user_id'=>$user,'body'=>$request->body]]]
    );

       // $notification = new NotificationController();

        $data = [
            "curr_user_id" => $decoded->data->id,
            "body" => $request->body,
            "post"=> $request->id
        ];

        (new NotificationController)->send_notification($data);

        $m = [
            "status"=>"Success",
            "message"=>"Comment was Successfully Posted"
        ];
        return response()->success($m,201);
       }
        catch(Exception $e)
        {
            return response()->error($e,400);
        }
        }
    public function update(Request $request)
    {
        $jwt = $request->bearerToken();
        try
        {
        $user = (new JwtController)->jwt_decode($jwt);
        $comment = (new mongodb)->laravel_project->posts->findOne(["comment.user_id"=>$user->data->id]);
        // dd($comment->comment[0]->body);// Displaying the body data
        foreach($comment->comment as $com)
        {
            if(($com->_id==new \MongoDB\BSON\ObjectId($request->comment_id))&&($com->user_id==$user->data->id))
            {
                $comment = (new mongodb)->laravel_project->posts->updateOne(["comment._id"=>new \MongoDB\BSON\ObjectId($request->comment_id)],
                ['$set'=>['comment.$.body'=>$request->body,]]);
                $m = [
                    "status"=> "success",
                    "message"=>"Comment Updated Successfully"
                ];
                return response()->success($m,201);
            }

        }
        $m = [
            "status"=> "failed",
            "message"=>"Either comment does not exist or your not the owner of the comment"
        ];
        return response()->error($m,403);

        }
        catch(Exception $e)
        {
            return response()->error($e->getMessage(),400);
        }
    }
    public function delete(Request $request)
    {
        $jwt = $request->bearerToken();
        try
        {
        $user = (new JwtController)->jwt_decode($jwt);
        $comment = (new mongodb)->laravel_project->posts->findOne(["comment.user_id"=>$user->data->id]);
        // dd($comment->comment[0]->body);// Displaying the body data
        foreach($comment->comment as $com)
        {
            if(($com->_id==new \MongoDB\BSON\ObjectId($request->comment_id))&&($com->user_id==$user->data->id))
            {
                $comment = (new mongodb)->laravel_project->posts->updateOne(["comment._id"=>new \MongoDB\BSON\ObjectId($request->comment_id)],
                ['$pull'=>['comment'=>["_id"=>new \MongoDB\BSON\ObjectId($request->comment_id)]]]
            );
                $m = [
                    "status"=> "success",
                    "message"=>"Comment Deleted Successfully"
                ];
                return response()->success($m,201);
            }

        }
        $m = [
            "status"=> "failed",
            "message"=>"Either comment does not exist or your not the owner of the comment"
        ];
        return response()->error($m,403);

        }
        catch(Exception $e)
        {
            return response()->error($e->getMessage(),400);
        }
}
}
