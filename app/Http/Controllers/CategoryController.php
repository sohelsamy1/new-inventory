<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function categoryPage(){
        return view ('pages.dashboard.category-page');
    }

    public function CategoryList(Request $request){
        $user_id=$request->header('user_id');
        return Category::where('user_id',$user_id)->get();
    }

    public function CategoryCreate(Request $request){
        $user_id=$request->header('user_id');
        return Category::create([
            'name'=>$request->input('name'),
            'user_id'=>$user_id
        ]);
    }

    public function CategoryDelete(Request $request){
        $category_id=$request->input('id');
        $user_id=$request->header('user_id');
        return Category::where('id',$category_id)->where('user_id',$user_id)->delete();
    }
}
