<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function signup(Request $request)
    {


        $validate = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email',
            'password'=> 'required'
        ]);
        //event(new Registered($request->email));
        // $token = $validate->createToken('token-name');
        // return $token->plainTextToken;
        $validate['password'] = bcrypt($validate['password']);
        //dd($validate);
        $user = [
            'name' => $request->name,
            'info' => 'Press the Following Link to Verify Email',
            'Verification_link'=>url('api/verifyEmail/'.$validate['email'])
        ];

        \Mail::to($request->email)->send(new \App\Mail\NewMail($user));
      $result =  User::create($validate);
        return $result;
    }

    public function login(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required|email',
            'password'=> 'required'
        ]);


        if(Auth::attempt(['email' => $validate["email"], 'password' => $validate["password"]]))
        {
        $user = auth()->user();

        if($user->verify)
        {
        $key = "example_key";
        $data = [
            "id"=>$user->id,
            "email"=>$user->email,
            "password"=>$validate["password"]
        ];
        $payload = array(
            "iss" => "http://localhost.com",
            "aud" => "http://localhost.com",
            "iat" => time(),
            "nbf" => time(),
            "data"=> $data
);



        $jwt =  JWT::encode($payload, $key, 'HS256');

         $user->remember_token=$jwt;

         User::where("email",$user->email)->update(["remember_token"=>$jwt]);

         $success = [
             "status"=>"success",
             "token"=> $jwt
         ];
         return response()->json($success);

        }
        else{
            return response()->json(["status"=>"failed","message"=>"Your mail is not verified"]);
        }
    }

        else
            {
                echo "Either email or password was wrong";
            }
            }

            public function verify($email)
            {
                if(User::where("email",$email)->value('verify') == 1)
                {
                    $m = ["You have already verified your account"];
                    return response()->json($m);
                }
                else
                {
                    $update=User::where("email",$email)->update(["verify"=>1]);
                    if($update){
                        return "ture";
                    }else{
                        return false;
                    }
                }
            }

}
