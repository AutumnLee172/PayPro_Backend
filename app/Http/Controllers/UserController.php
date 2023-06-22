<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $created = false;
        $error = "";
        try {
           //check if email existed
            $email = User::where('email', $request->get('email'))->get()->count();
            if($email > 0){
                $created = false;
                return response()->json([
                    'status' => true,
                    'created' => $created,
                    'error' => "Email was registered before.",
                ]);
                return;
            }

            $user = new User;
            $user->email = $request->get('email');
            $user->name = $request->get('name');
            $user->phone_number = $request->get('phone_number');
            $user->password = bcrypt($request->get('password'));
            $user->user_type = "Consumer";
            if($user->save()){
                $created = true;
            }else{
                $created = false;
            }  

        } catch (Throwable $e) {
           $error = $e;
        }
        
        return response()->json([
            'status' => true,
            'created' => $created,
            'error' => $error,
        ]);
    }

    public function login(Request $request)
    {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $token = $user->createToken('MyApp')->accessToken;
        return response()->json(['token' => $token,  'authorized' => true, 'userid' => $user->id, 'name' => $user->name, 'email' => $user->email, 'usertype' => $user->user_type, 'phonenumber' => $user->phone_number,], 200);
    }

    return response()->json(['authorized' => false, 'error' => 'Unauthorized']);
    }

    public function register_merchant(Request $request)
    {
        $created = false;
        $error = "";
        try {
           //check if email existed
            $email = User::where('email', $request->get('email'))->get()->count();
            if($email > 0){
                $created = false;
                return response()->json([
                    'status' => true,
                    'created' => $created,
                    'error' => "Email was registered before.",
                ]);
                return;
            }

            $user = new User;
            $user->email = $request->get('email');
            $user->name = $request->get('name');
            $user->phone_number = $request->get('phone_number');
            $user->password = bcrypt($request->get('password'));
            $user->user_type = "Merchant";
            if($user->save()){
                $created = true;
            }else{
                $created = false;
            }  

        } catch (Throwable $e) {
           $error = $e;
        }
        
        return response()->json([
            'status' => true,
            'created' => $created,
            'error' => $error,
        ]);
    }

}
