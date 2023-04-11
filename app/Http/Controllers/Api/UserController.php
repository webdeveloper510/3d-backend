<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class UserController extends Controller
{
    public $successStatus = 200;
        /**
        * login api
        *
        * @return \Illuminate\Http\Response
        */


    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
        if ($validator->fails()){
                return response()->json([
                'error'=>$validator->errors()], 401);
            }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('threeApp')->accessToken;
        $success['name'] =  $user->name;
        return response()->json(['success'=>$success], $this-> successStatus);
    }

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

    public function userDetails(){
        $user = Auth::user();
        return response()->json(['user' => $user], $this-> successStatus);
    }

    public function logout(){
        $user = Auth::user()->token();
        $user->revoke();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function addProduct(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'title' => 'required',
            'tag' => 'required',
            'description' => 'required',
            'embed_code' => 'required',
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

    public function deleteProducts($id){
        $data = product::find($id);
        $data->delete();
        return response()->json([
            'Product' => $data,
            'Message' => 'Deleted Product Successfully !!'
        ]);
    }

    public function editProducts(Request $request, $id){
        $name = $request['name'];
        $title = $request['title'];
        $tag = $request['tag'];
        $description= $request['description'];
        $image = $request['image'];
        $embed_code = $request['embed_code'];

        product::where('id', $id)->update(['name' => $name, 'title' => $title, 'tag' => $tag,'description' => $description, 'image' => $image, 'embed_code' => $embed_code]);
        $updated_data=product::find($id);
        return response()->json([
            'Updated Products' => $updated_data,
            'message' => 'Product Updated Successfully !!'
        ]);
    }

    public function getUsers(){
        $users = User::all();
        return response()->json(['All Users' => $users], 200, [], JSON_NUMERIC_CHECK);
    }

    public function deleteUser($id){
        $data = User::find($id);
        $data->delete();
        return response()->json([
            'Message' => 'Deleted user successfully !!',
            'User' => $data
        ]);
    }

    public function editUser(Request $request, $id){
        $data = $request->all();
        $name = $data['name'];
        $email = $data['email'];
        $password= bcrypt($data['password']);

        $update = User::where('id', $id)->update(['name' => $name, 'email' => $email, 'password' => $password]);

        return response()->json([
            'message' => 'User Updated Successfully !!',
            'User' => $update,
        ]);
    }

}

