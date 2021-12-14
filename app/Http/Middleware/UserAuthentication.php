<?php

namespace App\Http\Middleware;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\key;
use Closure;
use Illuminate\Http\Request;

class UserAuthentication
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
        try{
            $token=$request->bearerToken();
            if (User::where("remember_token", $token)->exists()){
                    $secret = config('constant.secret_key');
                    $decoded_data = JWT::decode($token,new Key($secret,'HS256'));
                    $user_data = $decoded_data->data;
                    $request=$request->merge(array('data' => $user_data));
                    return $next($request);
            }else{
                return response()->error("Token expired!",400);
            } 
        }catch(\Exception $e){
            return response()->error($e->getMessage(),400);
        }
    }
}
