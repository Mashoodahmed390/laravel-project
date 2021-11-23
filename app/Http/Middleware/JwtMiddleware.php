<?php

namespace App\Http\Middleware;
// use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\JwtController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;
use Auth;
use MongoDB\Client as mongodb;
class JwtMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $key = "example_key";
        JWT::$leeway = 60;
        try {
            $decoded= (new JwtController)->jwt_decode($request->bearerToken());

            $user = (new mongodb)->laravel_project->users->find(['email'=>$decoded->data->email])->toArray();

            if($user[0]->verify==1)
            {
                if(!isset($user))
                {
                    return response()->json(['status' => 'Not a valid user token']);
                }
                else
                {
                    if (!Hash::check($decoded->data->password, $user[0]->password)) {
                        return response()->json(['status' => 'Not a valid user token']);
                    }
                }
            }
            else
            {
                return response()->json(['error' => 'Please verify the link first'], 401);
            }

        } catch (Exception $e) {
            if($e instanceof \MongoDB\Client )
            if ($e instanceof \Firebase\JWT\SignatureInvalidException){
                return response()->json(['status' => 'Token is Invalid']);
            }else if ($e instanceof \Firebase\JWT\ExpiredException){
                return response()->json(['status' => 'Token is Expired']);
            }else{
                return response()->json(['status' => "Authorization Token not found"]);
            }

           return response()->json(['status' => $e->getMessage()]);
        }
        return $next($request);
    }
}
