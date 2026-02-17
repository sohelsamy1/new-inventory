<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function userRegistrationPage(){
        return view('pages.auth.registration-page');
    }

     public function userLoginPage(){
        return view('pages.auth.login-page');
    }

     public function resetPasswordPage(){
        return view('pages.auth.reset-pass-page');
    }
}
