<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use MongoDB\Client as mongodb;
use Exception;

class UserController extends Controller
{
    public function signup(UserRequest $request)
    {
        try
        {
        $validated = $request->validated();
        $validated['password'] = bcrypt($validated['password']);

        $result = (new mongodb)->laravel_project->users;
        $r = $result->insertOne([
            'name' => $validated["name"],
            'email' => $validated["email"],
            'password' => $validated["password"],
            'verify' => 0
        ]);
        $user = [
            'name' => $validated['name'],
            'info' => 'Press the Following Link to Verify Email',
            'Verification_link'=>url('api/verifyEmail/'.$validated['email'])
        ];

        \Mail::to($request->email)->send(new \App\Mail\NewMail($user));

        $message = "Sign up successful";
        return response()->success($message,200);
         }
         catch(Exception $e)
         {
             return response()->error($e->getMessage(),400);
         }
    }

    public function login(LoginRequest $request)
    {
        $validate = $request->validated();
        $result = (new mongodb)->laravel_project;
        $user = $result->users->find(["email"=>$validate["email"]])->toArray();
        //dd($user[0]->_id);
        //If there is no such user in database
        if(empty($user))
        {
            $response = [
                "status"=>"failed",
                "message"=>"no such user exists in database"
            ];

            return response()->error($response,400);
        }

        if(Hash::check($validate["password"], $user[0]->password))
        {
            if($user[0]->verify)
            {
            $data = [
                "id"=>(string)$user[0]->_id,
                "email"=>$validate["email"],
                "password"=>$validate["password"]
            ];

            $jwt = (new JwtController)->jwt_encode($data);

            $result->users->updateOne(
                ['email'=>$validate['email']],
                ['$set'=> ['jwt'=>$jwt]]

            );
            //$user->remember_token=$jwt;
            //User::where("email",$user->email)->update(["remember_token"=>$jwt]);

            $response = [
                "status"=>"success",
                "token"=> $jwt
            ];
            return response()->success($response,200);

        }
        else{
            $response = ["status"=>"failed","message"=>"Your mail is not verified"];
            return response()->error($response,403);
        }
    }

        else
            {
                $response = ["status"=>"failed","message"=>"Either email or Password was wrong"];
                return response()->error($response,400);
            }
            }

            public function verify($email)
            {
                $result = (new mongodb)->laravel_project;
                $user = $result->users->find(["email"=>$email])->toArray();
                if($user[0]->verify == 1)
                {
                    $response = [
                        "status"=>"failed",
                        "message"=>"You have already verified your account"];
                    return response()->error($response,403);
                }
                else
                {
                    $result->users->updateOne(
                        ['email'=>$email],
                        [
                         '$set'=>[ 'verify'=> 1 ]
                        ]
                    );
                        $response = [
                            "status"=>"success",
                            "message"=>"Account Verified"
                        ];
                        return response()->success($response,200);

                }
            }

}
