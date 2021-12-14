<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;

Route::post("upload_image",[ImageController::class,'uploadImage'])->middleware('UserAuthentication');
Route::get("all_images",[ImageController::class,'showAllImages'])->middleware('UserAuthentication');
Route::get("hidden_images",[ImageController::class,'listHiddenImages'])->middleware('UserAuthentication');
Route::get("private_images",[ImageController::class,'listPrivateImages'])->middleware('UserAuthentication');
Route::get("public_images",[ImageController::class,'listPublicImages'])->middleware('UserAuthentication');

Route::post("image_visibility",[ImageController::class,'setImageVisibility'])->middleware('UserAuthentication');
Route::post("delete_image",[ImageController::class,'deleteImage'])->middleware('UserAuthentication')->middleware('UserAuthentication');
Route::post("filter_images",[ImageController::class,'filterImages'])->middleware('UserAuthentication')->middleware('UserAuthentication');
Route::post("share_image",[ImageController::class,'shareImage'])->middleware('UserAuthentication')->middleware('UserAuthentication');


Route::post("viewSharedImage/{image_id}",[ImageController::class,'viewSharedImage'])->middleware('ImageAuthorization');;