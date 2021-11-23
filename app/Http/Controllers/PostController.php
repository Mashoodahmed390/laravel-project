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
use App\Http\Requests\DeleteRequest;
use App\Http\Requests\UpdatePostRequest;
use MongoDB\Client as mongodb;
use Exception;

use function PHPUnit\Framework\throwException;

class PostController extends Controller
{
    public function post(StorePostRequest $request)
    {
        $jwt = $request->bearerToken();
        $user = (new JwtController)->jwt_decode($jwt);
        $user = (new mongodb)->laravel_project->users->find(['email'=>$user->data->email])->toArray();
        $validated = $request->validated();
        if(($user[0]->jwt)==$jwt)
        {
        $result = (new mongodb)->laravel_project->posts;
        $id = $result->insertOne([
        "user_id"=>(string)$user[0]->_id,
        'body'=>$validated["body"],
        "path"=>null,
        "visiability"=>0,
        "comment"=>[]
        ]);
        $id = $id->getInsertedId();
        if(!empty($validated["file"]))
        {
        $image = $validated["file"];  // your base64 encoded
        $imageName = Str::random(10) . '.jpg';
        $path = 'storage/path/public/'.$imageName;
        //Storage::disk('local')->put($imageName, base64_decode($image));

        $result->updateOne(
            ['_id'=>$id],
            ['$set'=> ['path'=>$path]]

        );
        }

        $m = ["status"=>"success",
              "message"=>"Post submited"
    ];
        return response()->success($m,200);

        }
    }

    public function update(UpdatePostRequest $request,$id)
    {
        $jwt = $request->bearerToken();
        try
        {
        $decoded = (new JwtController)->jwt_decode($jwt);
        $post = (new mongodb)->laravel_project->posts;
        $v = $post->findOne(
            ['$and'=>[
            ["_id"=>new \MongoDB\BSON\ObjectId($id)],
            ["user_id"=>$decoded->data->id]
            ]]);

           if(!isset($v))
           {
            throw new Exception("Posts Not Exist");
           }
        if($v->user_id == $decoded->data->id)
        {
            if($request->has('body'))
            {
            $post->updateOne(["_id"=>new \MongoDB\BSON\ObjectId($id)],
            ['$set'=>['body'=>$request->body]]
        );
            }
            // if($request->has('file'))
            // {
            //     $post
            // }

            $m = [
                "Status"=> "Submitted",
                "Message"=>"Post was Updated"
            ];

            return response()->success($m,201);
        }
        else{
            $m = "your not the owner of this Post";
            return response()->error($m,403);
        }
    }
    catch(Exception $e)
    {
        return $e->getMessage();
    }
        // $user = User::find(1);
        // dd($user->post[0]->id);
        //dd($user->post()->get());
        // dd($post[0]->body);

      //  return response( )->json($user->post());
    }

    public function delete(DeleteRequest $request,$id)
    {
        $jwt = $request->bearerToken();
        try
        {
        $decoded = (new JwtController)->jwt_decode($jwt);
        $post = (new mongodb)->laravel_project->posts;
        $v = $post->findOne(
            ['$and'=>[
            ["_id"=>new \MongoDB\BSON\ObjectId($id)],
            ["user_id"=>$decoded->data->id]
            ]]);

           if(!isset($v))
           {
            throw new Exception("Posts Not Exist");
           }

        if($v->user_id == $decoded->data->id)
        {
            $post = (new mongodb)->laravel_project->posts->findOneAndDelete(['_id'=>new \MongoDB\BSON\ObjectId($id)]);
            $m = "Post Deleted";
            return response()->success($m,200);
        }
        else
        {
            $m = [
                "message"=>"This is not your Post so therefore u Cant delete"
            ];
            return response()->json($m);
        }
      }
      catch(Exception $e)
      {
          return response()->error($e->getMessage(),500);
      }

    }

    public function get_post(Request $r,$id)
    {
        $jwt = $r->bearerToken();

        try
        {
        $decoded = (new JwtController)->jwt_decode($jwt);
        $post = (new mongodb)->laravel_project->posts->findOne(["_id"=>new \MongoDB\BSON\ObjectId($id)]);
        $post_owner= $post->user_id;
        $post_owner = (new mongodb)->laravel_project->users->findOne(["_id"=>new \MongoDB\BSON\ObjectId($post_owner)]);
        $check_friend = (new mongodb)->laravel_project->users->findOne(["_id"=>new \MongoDB\BSON\ObjectId($decoded->data->id)]);
        foreach($check_friend->friend as $fri)
        {
            if(($fri->friend_id==$post_owner))
            {
                dd("all done");
                $data = [
                    "post" => $post->body,
                    "comment" =>$post->comment
                ];

                return response()->success($data,200);
            }
        }
        if(($post->visiability == 0) || ($decoded->data->id == $post->user_id))
        {
        $data = [
            "post" => $post->body,
            "comment" =>$post->comment
        ];

        return response()->success($data,200);

        }
        else{
            $m = [
                'status'=>'Denied',
                'message'=>'The post is private'
            ];

            return response()->json($m);
        }
       }catch(Exception $e)
       {
           return response()->error($e->getMessage(),400);
       }

    }
}
