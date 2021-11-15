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

class PostController extends Controller
{
    public function post(StorePostRequest $request)
    {
        $jwt = $request->bearerToken();
        // $key = "example_key";
        // $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        // dd($decoded);
        //dd(User::where("remember_token",$jwt)->exists());
        //dd(User::where("remember_token",$jwt)->value("remember_token") == $jwt);
        if(User::where("remember_token",$jwt)->exists())
        {

        $key = "example_key";
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

        $user = User::where('email',$decoded->data->email)->first();
        $image = $request->file;  // your base64 encoded
        $imageName = Str::random(10) . '.jpg';
        $path = 'storage/path/public/'.$imageName;

        //Storage::disk('local')->put($imageName, base64_decode($image));

        $p = new Post();
        $p->body = $request->body;
        $p->file = $path;

        $user->post()->save($p);

        // Another way to add data in database
        // $data = DB::table('posts')->insert([
        //     "body" => $request->body,
        //     "user_id"=> $decoded->data->id,
        //     "file"=> $path,
        // ]);


        $m = ["status"=>"success",
              "message"=>"Post submited"
    ];
        return response()->json($m);

        }
        else{
            dd('get out plz');
        }
    }

    public function update(Request $request,$id)
    {
        $jwt = $request->bearerToken();
        $key = "example_key";
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

        //dd(Post::where('user_id',$decoded->data->id)->exists());
        if($request->id == $decoded->data->id)
        {
            $user = User::where('email',$decoded->data->email)->first();

            $post = Post::find($id);
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

            return response()->json($m);
        }
        else{
            $m = [
                "message"=> "your not the owner of this Post"
            ];
            return response()->json($m);
        }
        // $user = User::find(1);
        // dd($user->post[0]->id);
        //dd($user->post()->get());
        // dd($post[0]->body);

      //  return response( )->json($user->post());
    }

    public function delete(Request $request,$id)
    {
        $jwt = $request->bearerToken();
        $key = "example_key";
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

        if($request->id == $decoded->data->id)
        {
            $user = User::where('email',$decoded->data->email)->first();
            $user->post()->whereId($id)->delete();

            $m = [
                "message"=>"Post Deleted"
            ];

        }
        else
        {
            $m = [
                "message"=>"This is not your Post so therefore u Cant delete"
            ];
            return response()->json($m);
        }


    }

    public function get_post(Request $r,$id)
    {
        $jwt = $r->bearerToken();
        $key = "example_key";
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
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
}
