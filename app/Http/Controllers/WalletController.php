<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;

class WalletController extends Controller
{
    public function new(Request $request)
    {
        $created = false;
        $error = "";
        try {
            $wallet = new Wallet;
            $wallet->userid = $request->get('userid');
            $wallet->wallet_type = $request->get('walletname');
            $wallet->balance = number_format((float)rand(50000, 500000) / 100, 2, '.', '');
            if($wallet->save()){
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

    public function get(Request $request){
        $userid = $request->get('userid');
        $wallets = wallet::where('userid',$userid)->get();
        return response()->json([
            'status' => true,
            'data' => $wallets
        ]);
    }
}
