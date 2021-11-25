<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtController extends Controller
{
    public function jwt_encode($data)
    {
        $key = config("constant.secret");
        $payload= array(
            "iss" => "http://localhost.com",
            "aud" => "http://localhost.com",
            "iat" => time(),
            "exp" => time()+3600,
            "data" =>$data
);
            try
           {
                $token = JWT::encode($payload,$key,'HS256');
                return $token;
           }
            catch(Exception $e)
            {
                return array('error'=>$e->getMessage());
            }

    }
    public function jwt_decode($token)
         {
            $key = config("constant.secret");
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        return $decoded;
        
    }
}
