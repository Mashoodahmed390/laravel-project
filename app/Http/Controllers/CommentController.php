<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;

class CommentController extends Controller
{
    public function comment(Request $request)
    {
        $jwt = $request->bearerToken();
        $key = "example_key";
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

        $user = User::where("email",$decoded->data->email)->first();
        $post = Post::where("id",$request->id)->first();
        $comment = new Comment();
        $comment->body = $request->body;

        $comment->post()->associate($request->id);
        $comment->user()->associate($user->id);

        // $post->comment()->save($comment);
        // $user->comment()->save($comment);

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

        return response()->json($m);
    }

    public function update(Request $request)
    {
        $jwt = $request->bearerToken();
        $key = "example_key";
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

        $comment = Comment::where('id',$request->comment_id)->where('post_id',$request->post_id)->first();

        //dd($comment->body); Displaying the body data

        if(!isset($comment))
        {
            $m = [
                "status"=>"failed",
                "message"=>"no such comment exist"
            ];

            return response()->json($m);
        }
        if(($comment->user_id == $decoded->data->id))
        {
            $comment->body = $request->body;
            $comment->save();

            $m = [
                "status"=> "success",
                "message"=>"Comment Updated Successfully"
            ];
            return response()->json($m);
        }
        else
        {
            $m = [
                "status"=> "failed",
                "message"=>"Your not the owner of this comment"
            ];
        }
    }
    public function delete(Request $request)
    {
        $jwt = $request->bearerToken();
        $key = "example_key";
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

        $comment = Comment::where('id',$request->comment_id)->where('post_id',$request->post_id)->first();

        //dd($comment->body); Displaying the body data

        if(!isset($comment))
        {
            $m = [
                "status"=>"failed",
                "message"=>"no such comment exist"
            ];

            return response()->json($m);
        }
        if(($comment->user_id == $decoded->data->id))
        {
            $comment->delete();

            $m = [
                "status"=> "success",
                "message"=>"Comment Deleted Successfully"
            ];
            return response()->json($m);
        }
        else
        {
            $m = [
                "status"=> "failed",
                "message"=>"Your not the owner of this comment"
            ];
        }
    }
}
