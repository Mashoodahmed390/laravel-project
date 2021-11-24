<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentDeleteRequest;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\CommentUpdateRequest;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Exception;

class CommentController extends Controller
{
    public function comment(CommentRequest $request)
    {
        try
        {
        $jwt = $request->bearerToken();
        $decoded = (new JwtController)->jwt_decode($jwt);
        $user = User::where("id",$decoded->data->id)->first();
        $post = Post::where("id",$request->id)->first();
        $comment = new Comment();
        $comment->body = $request->body;
        $comment->post()->associate($request->id);
        $comment->user()->associate($user->id);
        $comment->save();
        $notification = new NotificationController();
        $current_user_comment_id = $decoded->data->id;
        $msg = $request->body;
        $post_owner_id = $post->user_id;
        $data = [
            "curr_user_id" => $current_user_comment_id,
            "body" => $msg,
            "post_id" => $post_owner_id,
            "post"=> $request->id
        ];
        $notification->send_notification($data);
        $m = [
            "status"=>"Success",
            "message"=>"Comment was Successfully Posted"
        ];
        return response()->success($m,201);
        }
        catch(Exception $e)
        {
            return response()->error($e->getMessage(),400);
        }
    }

    public function update(CommentUpdateRequest $request)
    {
        try
        {
        $jwt = $request->bearerToken();
        $decoded = (new JwtController)->jwt_decode($jwt);
        $comment = Comment::where('id',$request->comment_id)->where('post_id',$request->post_id)->first();
        if(!isset($comment))
        {
            $m = [
                "status"=>"failed",
                "message"=>"no such comment exist"
            ];

            return response()->error($m,404);
        }
        if(($comment->user_id == $decoded->data->id))
        {
            $comment->body = $request->body;
            $comment->save();
            $m = [
                "status"=> "success",
                "message"=>"Comment Updated Successfully"
            ];
            return response()->success($m,201);
        }
        else
        {
            $m = [
                "status"=> "failed",
                "message"=>"Your not the owner of this comment"
            ];
            return response()->error($m,403);
        }
        }
        catch(Exception $e)
        {
            return response()->error($e->getMessage(),400);
        }
    }
    public function delete(CommentDeleteRequest $request)
    {
        try
        {
        $jwt = $request->bearerToken();
        $decoded = (new JwtController)->jwt_decode($jwt);
        $comment = Comment::where('id',$request->comment_id)->where('post_id',$request->post_id)->first();
        if(!isset($comment))
        {
            $m = [
                "status"=>"failed",
                "message"=>"no such comment exist"
            ];
            return response()->error($m,404);
        }
        if(($comment->user_id == $decoded->data->id))
        {
            $comment->delete();
            $m = [
                "status"=> "success",
                "message"=>"Comment Deleted Successfully"
            ];
            return response()->success($m,201);
        }
        else
        {
            $m = [
                "status"=> "failed",
                "message"=>"Your not the owner of this comment"
            ];
            return response()->error($m,403);
        }
            }
            catch(Exception $e)
            {
                return response()->error($e->getMessage(),400);
            }
    }

}
