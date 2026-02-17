<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function userRegistration(){
        return view('pages.auth.registration-page');
    }

     public function userLogin(){
        return view('pages.auth.login-page');
    }
}
