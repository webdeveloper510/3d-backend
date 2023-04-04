<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; 
use App\Models\product; 
use Illuminate\Support\Facades\Auth; 
use Validator;
class UserController extends Controller
{
       public $successStatus = 200;
    /** 
        * login api 
        * 
        * @return \Illuminate\Http\Response 
        */ 
       public function login(){ 
           if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){ 
               $user = Auth::user(); 
               $success['token'] =  $user->createToken('threeApp')-> accessToken; 
               $success['userId'] = $user->id;
               return response()->json(['success' => $success], $this-> successStatus); 
           } 
           else{ 
               return response()->json(['error'=>'Unauthorised'], 401); 
           } 
       }
    
    /** 
        * Register api 
        * 
        * @return \Illuminate\Http\Response 
        */ 
       public function register(Request $request) 
       { 

         // print_r($request->all());die;
           $validator = Validator::make($request->all(), [ 
               'name' => 'required',
               'email' => 'required|email|unique:users',
               'password' => 'required',
               'c_password' => 'required|same:password',
           ]);
           if ($validator->fails()) { 
                return response()->json(['error'=>$validator->errors()], 401);            
            }
         $input = $request->all(); 
           $input['password'] = bcrypt($input['password']); 
           $user = User::create($input); 
           $success['token'] =  $user->createToken('threeApp')->accessToken; 
           $success['name'] =  $user->name;
         return response()->json(['success'=>$success], $this-> successStatus); 
       }
    
    /** 
        * details api 
        * 
        * @return \Illuminate\Http\Response 
        */ 
       public function userDetails() 
       { 
           $user = Auth::user(); 
           return response()->json(['user' => $user], $this-> successStatus); 
       }
       public function addProduct(Request $request) 
       {
        $validator = Validator::make($request->all(), [ 
            'name' => 'required',
            'title' => 'required',
            'tag' => 'required',
            'description' => 'required',
            'image' => 'required'
        ]);
        if ($validator->fails()) { 
             return response()->json(['error'=>$validator->errors()], 401);            
         } 
         $product = new product();
         $product->name = $request->name;
         $product->title = $request->title;
         $product->tag = $request->tag;
         $product->description = $request->description;
         $product->embed_code = $request->embed_code;
         $product->image = $request->image;
         $product->save();
         return response()->json(['success' => $product], $this-> successStatus); 
       }

       public function getProducts(){
          $product_information = product::all();
          return response()->json(['products' => $product_information], $this-> successStatus); 
       }
}
