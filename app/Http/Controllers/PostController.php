<?php

namespace App\Http\Controllers;

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
    public function post(Request $request)
    {
        $jwt = $request->bearerToken();
        //dd($jwt);
        // $key = "example_key";
        // $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        // dd($decoded);
        if(User::where("remember_token",$jwt)->value("remember_token") == $jwt)
        {

        $key = "example_key";
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

        $image = $request->file;  // your base64 encoded
        $imageName = Str::random(10) . '.jpg';
        $path = 'storage/path/public/'.$imageName;

        Storage::disk('local')->put($imageName, base64_decode($image));

        $data = DB::table('posts')->insert([
            "body" => $request->body,
            "user_id"=> $decoded->data->id,
            "file"=> $path,
        ]);

        $m = ["status"=>"success",
              "message"=>"Post submited"
    ];
        return response()->json($m);

        }
        else{
            dd('get out plz');
        }
    }
}
