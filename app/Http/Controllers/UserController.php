<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $status = false;

        //check if email existed
        // $email = User::where('email', $request->get('email'))->get()->count();
        // if($email > 0){
        //     return response()->json([
        //         'status' => true,
        //         'created' => false,
        //         'msg' => "Email existed!",
        //     ]);
        //     return;
        // }

        $user = new User;
        $user->email = $request->get('email');
        $user->name = $request->get('name');
        $user->phone_number = $request->get('phone_number');
        $user->password = bcrypt($request->get('password'));
        $user->user_type = "Consumer";
        if($user->save()){
            $status = true;
        }else{
            $status = false;
        }  

        return response()->json([
            'status' => $status,
            'created' => true,
            'data' => [
                'id' => $user->id
            ]
        ]);
    }

    public function login(Request $request)
    {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $token = $user->createToken('MyApp')->accessToken;
        return response()->json(['token' => $token,  'status' => true], 200);
    }

    return response()->json(['error' => 'Unauthorized'], 401);
    }
}
