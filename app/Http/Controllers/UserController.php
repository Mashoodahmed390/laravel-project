<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\Post;
use Exception;

class UserController extends Controller
{
    public function signup(UserRequest $request)
    {
        try
        {
        $validated = $request->validated();
        $validated['password'] = bcrypt($validated['password']);

        $user = [
            'name' => $validated['name'],
            'info' => 'Press the Following Link to Verify Email',
            'Verification_link'=>url('api/verifyEmail/'.$validated['email'])
        ];
      //  \Mail::to($request->email)->send(new \App\Mail\NewMail($user));
        dispatch(new \App\Jobs\SendEmailJob($request->email,$user));

         User::create($validated);
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
        try
        {
        $validate = $request->validated();
        $user = User::where("email",$validate["email"])->first();
        if(empty($user))
        {
            throw new Exception("either email or password is wrong");
        }
        if(Hash::check($validate["password"], $user->password))
        {
            if($user->verify)
            {
            $data = [
                "id"=>$user->id,
                "email"=>$validate["email"],
                "password"=>$validate["password"]
            ];
            $jwt = (new JwtController)->jwt_encode($data);
            User::where("email",$user->email)->update(["remember_token"=>$jwt]);
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
        catch(Exception $e)
        {
            return response()->error($e->getMessage(),400);
        }
            }

            public function verify($email)
            {
                if(User::where("email",$email)->value('verify') == 1)
                {
                    $m = ["You have already verified your account"];
                    return response()->error($m,404);
                }
                else
                {
                    $update=User::where("email",$email)->update(["verify"=>1]);
                    if($update){
                        return response()->success("Account verified",200);
                    }else{
                        return response()->error("Failed",400);
                    }
                }
            }
            public function update_user(Request $request)
            {
                try
                {
                $decoded = $request->decoded;
                $user = (new User)->find($decoded->data->id);
                if($request->has("name"))
                {
                    $user->name = $request->name;
                }
                if($request->has("password"))
                {
                    $user->name = $request->password;
                }
                if($request->has("email"))
                {
                    $user->name = $request->email;
                }
                $v = $user->save();
                if($v)
                {
                return response()->success('User updated',201);
                }
                else
                {
                    return response()->success('Please Enter in field to update',201);
                }

                }
                catch(Exception $e)
                {
                    return response()->error($e->getMessage(),401);
                }

            }
            public function resource(Request $request)
            {
                $decoded = $request->decoded;
                $user = User::find($decoded->data->id);
                return new UserResource($user);
            }
        }
