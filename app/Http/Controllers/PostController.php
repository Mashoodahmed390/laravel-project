<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Friend;
use App\Models\Post;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\JwtController;
use App\Http\Requests\UpdatePostRequest;
use Exception;

class PostController extends Controller
{
    public function post(StorePostRequest $request)
    {
        try
        {
        $jwt = $request->bearerToken();
        $p = new Post();
        $decoded = (new JwtController)->jwt_decode($jwt);
        $user = User::where('id',$decoded->data->id)->first();
        if($request->has('file'))
        {
        $image = $request->file;  // your base64 encoded
        $imageName = Str::random(10) . '.jpg';
        $path = 'storage/path/public/'.$imageName;
        //Storage::disk('local')->put($imageName, base64_decode($image));
        $p->file = $path;
        }
        $p->body = $request->body;


        $user->post()->save($p);

        $m = ["status"=>"success",
              "message"=>"Post submited"];
        return response()->success($m,201);
        }
        catch(Exception $e)
        {
            return response()->error($e->getMessage(),400);
        }
        }


    public function update(UpdatePostRequest $request,$id)
    {
        try
        {
        $jwt = $request->bearerToken();
        $post = Post::find($id);
        $decoded = (new JwtController)->jwt_decode($jwt);
        if($post->user_id == $decoded->data->id)
        {
            $user = User::where('email',$decoded->data->email)->first();
            if($request->has('body'))
            {
            $post->body = $request->body;

            }
            if($request->has('file'))
            {
                $post->file = $request->file;
            }
            $user->post()->save($post);

            $m = [
                "Status"=> "Submitted",
                "Message"=>"Post was Submitted"
            ];

            return response()->success($m,201);
        }
        else{
            $m = [
                "message"=> "your not the owner of this Post"
            ];
            return response()->error($m,403);
        }
        }
        catch(Exception $e)
        {
            return response()->error($e->getMessage(),400);
        }
    }

    public function delete(Request $request,$id)
    {
        try
        {
        $jwt = $request->bearerToken();
        $decoded = (new JwtController)->jwt_decode($jwt);
        $post = Post::find($id);
        if(!isset($post))
        {
            throw new Exception("Post does not exist");
        }
        if($post->user_id == $decoded->data->id)
        {
            $user = User::where('email',$decoded->data->email)->first();
            $user->post()->whereId($id)->delete();
            $m = [
                "message"=>"Post Deleted"
            ];
            return response()->success($m,201);
        }
        else
        {
            $m = [
                "message"=>"This is not your Post so therefore u Cant delete"
            ];
            return response()->error($m,403);
        }
        }
        catch(Exception $e)
        {
            return response()->error($e->getMessage(),400);
        }

    }

    public function get_post(Request $r,$id)
    {
        try
        {
        $jwt = $r->bearerToken();
        $decoded = (new JwtController)->jwt_decode($jwt);
        $post = Post::find($id);
        $friend = $post->user()->get();

        if(($post->privacy == 0) || (Friend::where([["user_id",$decoded->data->id],["email",$friend[0]->email]])->exists() || ($decoded->data->id == $post->user_id)))
        {
        $post_comment =$post->comment()->get();

        $data = [
            "post" => $post,
            "comment" =>$post_comment
        ];
        return response()->json($data);

        }
        else{
            $m = [
                'status'=>'Denied',
                'message'=>'The post is private'
            ];

            return response()->json($m);
        }
            }
            catch(Exception $e)
            {
                return response()->error($e->getMessage(),400);
            }
    }

}
