<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use DB;
use Carbon\Carbon;

class UserController extends Controller
{
    public $successStatus = 200;
    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'org_password' => 'required',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 401);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('threeApp')->accessToken;
        $success['name'] =  $user->name;
        return response()->json(['success' => $success], $this->successStatus);
    }

    public function login()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();
            $success['token'] =  $user->createToken('threeApp')->accessToken;
            $success['userId'] = $user->id;
            return response()->json(['success' => $success], $this->successStatus);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }


    public function getCounts()
    {
        // $users = User::all()->except(Auth::id());
        $users = User::count();
        $product = product::count();
        $publish = product::where('publish', 1)->count();
        $active = User::where('grant', 1)->count();
        return response()->json(['users' => $users, 'products' => $product, 'publish' => $publish, 'active' => $active]);
    }

    public function userDetails()
    {
        $user = Auth::user();
        return response()->json(['user' => $user], $this->successStatus);
    }

    public function logout()
    {
        $user = Auth::user()->token();
        $user->revoke();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'title' => 'required',
            'tag' => 'required',
            'description' => 'required',
            'embed_code' => 'required',
            'image' => 'required'
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        // print_r($request->all());die;

        $product = new product();

        $extension = $request->image->getClientOriginalName();
        $filename = time() . '.' . $extension;
        $request->image->move(public_path('images'), $filename);
        $product->image = $filename;

        $product->name = $request->name;
        $product->title = $request->title;
        $product->tag = $request->tag;
        $product->description = $request->description;
        $product->embed_code = $request->embed_code;
        $product->image = $filename;
        $product->publish = $request->published;
        $product->save();
        return response()->json(['success' => $product], $this->successStatus);
    }

    public function getProducts()
    {
        $product_information = product::all();
        return response()->json(['products' => $product_information], $this->successStatus);
    }

    public function deleteProducts($id)
    {
        $data = product::find($id);
        $data->delete();
        return response()->json([
            'Product' => $data,
            'Message' => 'Deleted Product Successfully !!'
        ]);
    }

    public function editProducts(Request $request, $id)
    {
        $product = product::find($id);
        $product->name = $request['name'];
        $product->title = $request['title'];
        $product->tag = $request['tag'];
        $product->description = $request['description'];
        $product->embed_code = $request['embed_code'];
        $product->publish = $request->published;
        if ($request->hasFile('image')) {
            $extension = $request->image->getClientOriginalName();
            $filename = time() . '.' . $extension;
            $request->image->move(public_path('images'), $filename);
            $product->image = $filename;
        }
        if ($product->save())
            return response()->json([
                'message' => 'Product Updated Successfully !!'
            ]);
    }

    public function editPublished(Request $request, $id)
    {
        $product = product::find($id);
        $product->publish = $request->published;
        if ($product->save())
            return response()->json([
                'message' => 'Published Successfully !!'
            ]);
    }


    public function editGrant(Request $request, $id)
    {

        // echo $id;
        // die;
        $users = User::find($id);

        $users->grant = $request->grant;
        if ($users->save())
            return response()->json([
                'message' => 'User Grant  Successfully !!'
            ]);
    }

    public function getUsers()
    {
        $users = User::all();
        return response()->json(['users' => $users], 200, [], JSON_NUMERIC_CHECK);
    }

    public function deleteUser($id)
    {
        $data = User::find($id);
        $data->delete();
        return response()->json([
            'Message' => 'Deleted user successfully !!',
            'User' => $data
        ]);
    }

    public function editUser(Request $request, $id)
    {
        $data = $request->all();
        $name = $data['name'];
        $email = $data['email'];
        //$password= bcrypt($data['password']);

        $update = User::where('id', $id)->update(['name' => $name, 'email' => $email]);

        return response()->json([
            'message' => 'User Updated Successfully !!',
            'User' => $update,
        ]);
    }


    public function readCsv(Request $request)
    {
        $data = [];
        $file = $request->file('file');
        $path = $file->store('temp');
        $reader = IOFactory::createReaderForFile(storage_path('app/' . $path));
        $spreadsheet = $reader->load(storage_path('app/' . $path));
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheetArray = $worksheet->toArray();
        array_shift($worksheetArray);
        foreach ($worksheetArray as $key => $value) {
            $worksheet = $spreadsheet->getActiveSheet();
            $drawing = $worksheet->getDrawingCollection()[$key];
            $zipReader = fopen($drawing->getPath(), 'r');
            $imageContents = '';
            while (!feof($zipReader)) {
                $imageContents .= fread($zipReader, 1024);
            }
            fclose($zipReader);
            $extension = $drawing->getExtension();
            $file_name = str_replace(' ', '_', $drawing->getName()) . '.' . $drawing->getExtension();
            Storage::put('public/images/' . '/'  . '/' . $file_name, file_get_contents($drawing->getPath()));
            $data[] = [
                'image' => $file_name,
                'description' => $value[2],
                'name' => $value[2],
                'title' => $value[2],
                'tag' => $value[2],
                'embed_code' => '',
                'publish' => 0,
            ];
        }

        product::insert($data);

        return response()->json([
            'data' => $data
        ]);
    }

    public function get_users_statisticks()
    {
        $users = User::select('id', 'created_at')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('m');
            });

        $usermcount = [];
        $userArr = [];

        foreach ($users as $key => $value) {
            $usermcount[(int)$key] = count($value);
        }

        $month = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        for ($i = 1; $i <= 12; $i++) {
            if (!empty($usermcount[$i])) {
                $userArr[$i]['count'] = $usermcount[$i];
            } else {
                $userArr[$i]['count'] = 0;
            }
            $userArr[$i]['month'] = $month[$i - 1];
        }

        return response()->json(array_values($userArr));
    }
    // public function get_users_statisticks()
    // {
    //     $data = User::selectRaw('COUNT(*) as count, YEAR(created_at) year, MONTHNAME(created_at) month')
    //         ->groupBy('year', 'month')
    //         ->get()->toArray();

    //     return response()->json([
    //         'get_users_statisticks' => $data
    //     ]);
    // }

    public function forget_password(Request $request)
    {
        $forget_password = User::where('email', $request->email)
            ->update(['password' => bcrypt($request['password']), 'org_password' => $request['password']]);
        if ($forget_password == 1) {
            return response()->json([
                'Message' => 'Password Reset Successfully !!'
            ]);
        } else {
            return response()->json([
                'Message' => 'Oops !! Somthing went wrong with email..'
            ]);
        }
    }
}
