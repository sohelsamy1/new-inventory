<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


// Frontend Routes
Route::get('/', [HomeController::class, 'homePage']);
Route::get('/dashboard', [DashboardController::class, 'dashboardPage']);
Route::get('/categoryPage', [CategoryController::class, 'categoryPage']);
Route::get('/userRegistration', [UserController::class, 'userRegistrationPage']);
Route::get('/userLogin', [UserController::class, 'userLoginPage']);
Route::get('/resetPassword', [UserController::class, 'resetPasswordPage']);
Route::get('/sendOtp', [UserController::class, 'sendOtpPage']);
Route::get('/verifyOtp', [UserController::class, 'verifyOtpPage']);
Route::get('/userProfile', [UserController::class, 'profilePage']);

// API /
Route::post('/registration', [UserController::class, 'userRegistration']);
Route::post('/login', [UserController::class, 'userLogin']);
Route::post('/send-otp', [UserController::class, 'sendOTP']);
