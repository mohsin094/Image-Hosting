<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UploadImageRequest;
use App\Http\Requests\ImageVisibilityRequest;
use App\Http\Requests\DeleteImageRequest;
use App\Http\Requests\SharedImageRequest;
use App\Models\User;
use App\Models\Image;
use App\Jobs\SendEmailJob;
class ImageController extends Controller
{
    public function uploadImage(UploadImageRequest $request){
        try{
            $userInput = $request->validated();
            $userData = $request->data;
            $user = User::find($userData->id);
            $image = new Image;

            if(!empty($userInput['picture'])){
                $pos  = strpos($userInput['picture'], ';');
                $type = explode(':', substr($userInput['picture'], 0, $pos))[1];
                $ext=explode('/',$type);
                $imageName=time().'.'.$ext[1];
                $img = preg_replace('/^data:image\/\w+;base64,/', '', $userInput['picture']);
                $path=storage_path('app\\public\\uploads').'\\'.$imageName;
                file_put_contents($path,base64_decode($img));
                $image->picture =  $imageName;
                $user->images()->save($image);
            }else{
                return response()->error('Please select an image!', 400);
            }
            return response()->success('Image upload successfully', 200);

        }catch(\Exception $ex){
            return response()->error($ex->getMessage(),400);
        }
    }


    public function showAllImages(Request $request){
        try{
            $userData = $request->data;
            $data['imgUrl'] = url('/storage/uploads');
            $data['image']= Image::where('user_id', $userData->id)->get('picture');
            return response()->success($data,200);

        }catch(\Exception $ex){
            return response()->error($ex->getMessage(),400);
        }
    }


    public function listHiddenImages(Request $request){
        try{
            $userData = $request->data;
            $data['imgUrl'] = url('/storage/uploads');
            $data['image']= Image::where('user_id', $userData->id)->where('visibile','hidden')->get('picture');
            return response()->success($data,200);

        }catch(\Exception $ex){
            return response()->error($ex->getMessage(),400);
        }
    }


    public function listPrivateImages(Request $request){
        try{
            $userData = $request->data;
            $data['imgUrl'] = url('/storage/uploads');
            $data['image']= Image::where('user_id', $userData->id)->where('visibile','private')->get('picture');
            return response()->success($data,200);

        }catch(\Exception $ex){
            return response()->error($ex->getMessage(),400);
        }
    }


    public function listPublicImages(Request $request){
        try{
            $userData = $request->data;
            $data['imgUrl'] = url('/storage/uploads');
            $data['image']= Image::where('user_id', $userData->id)->where('visibile','public')->get('picture');
            return response()->success($data,200);

        }catch(\Exception $ex){
            return response()->error($ex->getMessage(),400);
        }
    }


    public function setImageVisibility(ImageVisibilityRequest $request){
        try{
            $userData = $request->data;
            $userInput = $request->validated();

            if($userInput['visibility']=='hidden' ||$userInput['visibility']=='public' || $userInput['visibility']=='private' ){
                Image::where('user_id', $userData->id)->where('picture',$userInput['picture'])->update(['visibile' => $userInput['visibility']]);
                return response()->success("Image visibility set!",200);
            }else{
                return response()->error("Image visibility can only be hidden, public or private!",400);
            }

        }catch(\Exception $ex){
            return response()->error($ex->getMessage(),400);
        }
    }


    public function deleteImage(DeleteImageRequest $request){
        try{
            $userData = $request->data;
            $userInput = $request->validated();
            Image::where('user_id', $userData->id)->where('picture',$userInput['picture'])->delete();
            return response()->success("Image deleted successfully!",200);

        }catch(\Exception $ex){
            return response()->error($ex->getMessage(),400);
        }
    }


    public function filterImages(Request $request){
        try{
            $userData = $request->data;
            $data['imgUrl'] = url('/storage/uploads');            
            $data['image'] = Image::where('user_id', $userData->id)->where('picture', 'like', '%' . $request->filter . '%')->orwhere('created_at', 'like', '%' . $request->filter . '%')->get('picture');
          return response()->success($data,200);

        }catch(\Exception $ex){
            return response()->error($ex->getMessage(),400);
        }
    }


    public function shareImage(SharedImageRequest $request){
        try{
            $userData = $request->data;
            $userInput = $request->validated();
            $image =Image::find($userInput['image_id']);
            
            if($image['visible']=="private" || $image['visible']=="public"){
                $email_arr = explode (",",  $image['shared_email']);

                if(in_array($userInput['shared_email'], $email_arr)){
                    return response()->success("Already shared!.",400); 
                }
               
                $emails = $image['shared_email'].$userInput['shared_email'].",";
                $image->shared_email = $emails;
                $image->save();
               
                //send email
                $link = url('image/viewSharedImage/'.$image->id);
                ImageController::sendImageLink($userInput['shared_email'],$userData->name, $link);
                return response()->success("$link",200);  
            }else{
                return response()->success("It's not share able!",400); 
            }
        }catch(\Exception $ex){
            return response()->error($ex->getMessage(),400);
        }
    }

    public function sendImageLink($shared_email,$sender_name,$link){
       try{
        $detail = [
            'description' => $sender_name.' shared an image with you, click on link to check image!',
            'email' => $shared_email,
            'Link' => $link
            ];
            dispatch(new SendEmailJob($detail));

        } catch (\Exception $ex) {
            return response()->error($ex->getMessage(),400);
        }
    }

    public function viewSharedImage(Request $request,$image_id){
        try{
            $email = $request->email;
           // $userData = $request->image_name;
            $data['imgUrl'] = url('/storage/uploads');            
            $data['image'] = $request->image_name;
          return response()->success($data,200);
        } catch (\Exception $ex) {
            return response()->error($ex->getMessage(),400);
        }
    }

}