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

    public function login_merchant(Request $request)
    {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        if($user->user_type == "Merchant"){
            $token = $user->createToken('MyApp')->accessToken;
            return response()->json(['token' => $token,  'authorized' => true, 'userid' => $user->id, 'name' => $user->name, 'email' => $user->email, 'usertype' => $user->user_type, 'phonenumber' => $user->phone_number,], 200);
        }else{
            return response()->json(['authorized' => false, 'error' => 'Unauthorized']);
        }
       
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

    public function update_username(Request $request){
        $updated = false;
        $error = "";
        try {
        $email = ($request->has('email') && !empty($request->get('email'))) ? $request->get('email') : '';
        $newUsername = ($request->has('username') && !empty($request->get('username'))) ? $request->get('username') : '';

        $user = User::where('email', $email)->first();
        $user->name = $newUsername;
        if($user->save()){
            $updated = true;
        }else{
            $updated = false;
        }  
        } catch (Throwable $e) {
            $error = $e;
        }
     
        return response()->json([
            'status' => true,
            'updated' => $updated,
            'error' => $error,
        ]);
    }

    public function update_password(Request $request){
        $updated = false;
        $error = "";
        try {
        $email = ($request->has('email') && !empty($request->get('email'))) ? $request->get('email') : '';
        $password = ($request->has('password') && !empty($request->get('password'))) ? $request->get('password') : '';

        $user = User::where('email', $email)->first();
        $user->password = bcrypt($password);
        if($user->save()){
            $updated = true;
        }else{
            $updated = false;
        }  
        } catch (Throwable $e) {
            $error = $e;
        }
     
        return response()->json([
            'status' => true,
            'updated' => $updated,
            'error' => $error,
        ]);
    }

    public function getUsername(Request $request){
        $error = "";
        try {
            $email = ($request->has('email') && !empty($request->get('email'))) ? $request->get('email') : '';
    
            $user = User::where('email', $email)->first();
            
        } catch (Throwable $e) {
            $error = $e;
        }

        return response()->json([
            'status' => true,
            'username' => $user->name,
            'error' => $error,
        ]);
    }

}
