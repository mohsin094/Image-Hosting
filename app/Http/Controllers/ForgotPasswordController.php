<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests\ForgotRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Jobs\SendEmailJob;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\Authentication;

class ForgotPasswordController extends Controller
{
    public function forgetPassword(ForgotRequest $request){
        try{
            $userData = $request->validated();
            $new_password=uniqid();

            $user=User::where('email',$userData['email'])->first();
            $user->password=bcrypt($new_password);
            $user->save();
           
            ForgotPasswordController::sendForgotEmail($userData['email'],$new_password);
            return response()->success("Your new password successfully send to your email!",200);
        }catch(\Exception $ex){
            return response()->error($ex->getMessage(),400);
        }
    }

    public function sendForgotEmail($email,$new_password){
      try{
        $detail = [
            'description' => 'Your new password is.'.$new_password.'. \r\n Use this password to login. \r\n You can change your password any time.',
            'email' => $email,
            'Link' => url('user/redirectLogin')
            ];
            dispatch(new SendEmailJob($detail));
        }catch(\Exception $ex){
            return response()->error($ex->getMessage(),400);
        }       
    }

    public function redirectToLogin(){
        try{
            return response()->success("Login please",200);
        }catch(\Exception $ex){
            return response()->error($ex->getMessage(),400);
        } 

    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try{
            $userInput = $request->validated();
            $userData = $request->data;

            //$user = User::find($userData->id);
            if ($user = Auth::attempt(['id' => $userData->id, 'password' => $userInput['current_password']])) {
                $user = auth()->user();
                $user->password = bcrypt($userInput['new_password']);
                //$user->update();
                return response()->success("Password Updated",200);
            }else{
                return response()->error("Wrong credential",400);
            }
            
            }catch(\Exception $e)
           {
               return response()->error($e->getMessage(),404);
           }
    }

}
