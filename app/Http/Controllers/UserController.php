<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Jobs\SendEmailJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Authentication;

//user define imports
use App\Http\Requests\RegistrationRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Storage;
class UserController extends Controller
{
    public function registration(RegistrationRequest $request){
        try {
            $userInput= $request->validated();
            $user = new User;

            $user->name = $userInput['name'];
            $user->email = $userInput['email'];
            $user->password =  bcrypt($userInput['password']);
            $user->age = $userInput['age'];

                $pos  = strpos($userInput['picture'], ';');
                $type = explode(':', substr($userInput['picture'], 0, $pos))[1];
                $ext=explode('/',$type);
                
                $imageName=time().'.'.$ext[1];
                $img = preg_replace('/^data:image\/\w+;base64,/', '', $userInput['picture']);
                $path=storage_path('app\\public\\profile').'\\'.$imageName;
                file_put_contents($path,base64_decode($img));

                $user->picture = $imageName;
                $user->save();
            UserController::sendVerificationLink($userInput['name'], $userInput['email']);
            return response()->error('Please verify your account!', 200);
        } catch (\Exception $ex) {
            return response()->error($ex->getMessage(),400);
        }
    }

    public function sendVerificationLink($name, $email){
        try{
        $detail = [
            'description' => $name . ' click on link to verify your account!',
            'email' => $email,
            'Link' => url('user/verifyemail/'.$email)
            ];
            dispatch(new SendEmailJob($detail));

        } catch (\Exception $ex) {
            return response()->error($ex->getMessage(),400);
        }
    }

        //function to verify user
        public function verify($email)
        {
            try{
                User::where("email", $email)->update(["verified" => true, "email_verified_at" => date('Y-m-d H:i:s')]);  
                return response()->success('Your account is verified', 200);
            } catch (\Exception $ex) {
                return response()->error($ex->getMessage(),400);
            }
        }


        public function login(LoginRequest $request){
            try{
                $userInput =$request->validated();

                if ($user = Auth::attempt(['email' => $userInput['email'], 'password' => $userInput['password']])) {
                    $user = auth()->user();
                    $profilePicture= url('/storage/profile/'.$user->picture);

                    if (User::where('id', $user->id)->value('verified') == 1) {
                        $dataArray = [
                            "id" => $user->id,
                            "name" => $user->name,
                            "email" => $user->email,
                            "age" => $user->age,
                            "picture" => $profilePicture
                            ];
                        $authentication = new Authentication($dataArray);
                        $token = $authentication->getToken();

                        $user->remember_token = $token;
                        User::where("email", $user->email)->update(["remember_token" => $token]);

                        $userData = [
                            "token" => $token,
                            "data" => $dataArray,
                        ];
                        return response()->success($userData, 200);
                    } else {
                        UserController::sendVerificationLink($user->name, $user->email);
                        return response()->error('Please verify your account!', 400);
                    }
                } else {
                    return response()->error('Wrong credential!', 400);
                }

            }catch(\Exception $ex){
                return response()->error($ex->getMessage(),400);
            }
        }

        public function logout(Request $request){
            try{
                $userData = $request->data;
                User::where("id", $userData->id)->update(["remember_token" => null]);
                return response()->success('Logout Successfully!', 200);
            }catch(\Exception $ex){
                return response()->error($ex->getMessage(),400);
            }
        }


        public function updateProfile(UpdateProfileRequest $request){
            try{
                $userInput = $request->validated();
                $userData = $request->data;
                $user = User::find($userData->id);

                if(isset($userInput['picture'])){
                    $pos  = strpos($userInput['picture'], ';');
                    $type = explode(':', substr($userInput['picture'], 0, $pos))[1];
                    $ext=explode('/',$type);
                    
                    $imageName=time().'.'.$ext[1];
                    $img = preg_replace('/^data:image\/\w+;base64,/', '', $userInput['picture']);
                    $path=storage_path('app\\public\\uploads').'\\'.$imageName;
                    file_put_contents($path,base64_decode($img));
    
                    $user->picture = $imageName;
                }
                if(isset($userInput['name'])){
                    $user->name = $userInput['name'];
                }
                if(isset($userInput['age'])){
                    $user->age = $userInput['age'];
                }
                $user->save();

                return response()->success("Profile successfully updated!",200);
            }catch(\Exception $ex){
                return response()->error($ex->getMessage(),400);
            }
        }
}
