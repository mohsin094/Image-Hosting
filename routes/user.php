<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ForgotPasswordController;

Route::post("signup",[UserController::class,'registration']);
Route::get("verifyemail/{email}",[UserController::class,'verify']);
Route::post("signin",[UserController::class,'login']);
Route::post("logout",[UserController::class,'logout'])->middleware('UserAuthentication');
Route::post("update_profile",[UserController::class,'updateProfile'])->middleware('UserAuthentication');
Route::post("forget_password",[ForgotPasswordController::class,'forgetPassword']);
Route::post("reset_password",[ForgotPasswordController::class,'resetPassword'])->middleware('UserAuthentication');

Route::get("redirectLogin",[UserController::class,'redirectToLogin']);