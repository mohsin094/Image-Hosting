<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\key;

class ImageAuthorization
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
            $image_id = $request->image_id;
            $image =Image::find($image_id)->first();
            if(($image['visible'] == "public")){
               // $user_data = $decoded_data->data;
                $request=$request->merge(array('image_name' => $image->picture));
                return $next($request);
                    //return response()->success("$image->picture",200); 
                }elseif($image['visible'] == "private"){
                    $token=$request->bearerToken();
                    if (!empty($token) && User::where("remember_token", $token)->exists()){
                        $secret = config('constant.secret_key');
                        $decoded_data = JWT::decode($token,new Key($secret,'HS256'));
                        $user_data = $decoded_data->data;

                        $email_arr = explode (",", $image['shared_email']);
                        if(in_array($user_data->email, $email_arr)){
                            $request=$request->merge(array('image_name' => $image->picture));
                            return $next($request);
                            }else{
                                return response()->error("Accessibility error!",400);
                            }
                    }else{
                        return response()->error("Authorization error!",400);
                    } 
                }else{
                    return response()->error("Accessibility error!",400);
                }
        }catch(\Exception $e){
            return response()->error($e->getMessage(),400);
        }
        //return $next($request);
    }
}
