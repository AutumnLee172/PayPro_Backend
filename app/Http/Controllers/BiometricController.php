<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Biometric;
use App\Models\User;

class BiometricController extends Controller
{
    public function registerBiometric(Request $request){
        $created = false;
        $error = "";

        $email = ($request->has('email') && !empty($request->get('email'))) ? $request->get('email') : '';
        $hash = hash('sha256', $email);
        $Status = "Pending";
        $userid = User::where('email', $email)->pluck('id')->first();

        try{
            $biometric = new Biometric;
            $biometric->userid = $userid;
            $biometric->hash = $hash;
            $biometric->Status = $Status;

            if($biometric->save()){
                $created = true;
            }else{
                $created = false;
            }  

        }catch (Throwable $e) {
           $error = $e;
        }

        return response()->json([
            'status' => true,
            'created' => $created,
            'error' => $error,
        ]);
    }

    public function checkifPending(Request $request){

        $requireConfrimation = false;
        $biometric = Biometric::where('userid', $request->get('userid'))->where('Status', 'Pending')->count();
        if($biometric == 1){
            $requireConfrimation = true;
        }else{
            $requireConfrimation = false;
        }

        return response()->json([
            'status' => true,
            'requireConfrimation' => $requireConfrimation,
        ]);

    }

    public function approve(Request $request){
        $biometric = Biometric::where('userid', $request->get('userid'))->first();
        $biometric->Status = "Active";
        
        if($biometric->save()){
            $approved = true;
        }else{
            $approved = false;
        }  

        return response()->json([
            'status' => true,
            'approved' => $approved,
        ]);
    }

    public function decline(Request $request){
        $deleted = false;
        $biometric = Biometric::where('userid', $request->get('userid'))->delete();
        
        if($biometric){
            $deleted = true;
        }else{
            $deleted = false;
        }  

        return response()->json([
            'status' => true,
            'deleted' => $deleted,
        ]);
    }

    public function getPayerWallet(String $email){
        $hash = hash('sha256', $email);
        $wallet = Biometric::where('hash', $hash)->first();

        return $wallet;
    }
}
