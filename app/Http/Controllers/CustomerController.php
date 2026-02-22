<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerController extends Controller
{
     public function CustomerPage(){
        return view('pages.dashboard.customer-page');
    }
}
