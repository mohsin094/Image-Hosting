<?php
namespace App\Services;
use Firebase\JWT\JWT;

class Authentication
{
    protected $token;
    public function __construct($userData){
        try{
            $key =config('constant.secret_key');
                
            $payload = array(
                "iss" => "http://localhost.com",
                "aud" => "http://localhost.com",
                "iat" => time(),
                "nbf" => time(),
                "data"=> $userData
            );
            $this->token =  JWT::encode($payload, $key, 'HS256');
        }
        catch(\Exception $e){
            return response()->error($e->getMessage(),400);
        }
    }

    public function getToken(){
        return $this->token;
    }
}
