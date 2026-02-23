<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
     public function CustomerPage(){
        return view('pages.dashboard.customer-page');
    }

    public function CustomerCreate(Request $request){
        $user_id=$request->header('user_id');
        return Customer::create([
            'name'=>$request->input('name'),
            'email'=>$request->input('email'),
            'mobile'=>$request->input('mobile'),
            'user_id'=>$user_id
        ]);
    }
}
