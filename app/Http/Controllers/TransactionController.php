<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BiometricController;

class TransactionController extends Controller
{
    public function newInternal(Request $request)
    {
        DB::beginTransaction();
        $created = false;
        $error = "";
        try {
            //wallet balance update
            $from_wallet = Wallet::find($request->get('walletid'));
            if($from_wallet->balance < $request->get('amount')){
                $error = "Insufficient Balance";
            }else{
                $from_wallet->balance = $from_wallet->balance - (float) $request->get('amount');

                $to_wallet = Wallet::find($request->get('to_account'));
                $to_wallet->balance = $to_wallet->balance + (float) $request->get('amount');
                
                //new transaction
                $transaction = new Transaction;
                $transaction->userid = $request->get('userid');
                $transaction->walletid = $request->get('walletid');
                $transaction->wallet_type = $from_wallet->wallet_type;
                $transaction->amount = $request->get('amount');
                $transaction->to_account = $request->get('to_account');
                $transaction->to_wallet_type = $to_wallet->wallet_type;
                $transaction->reference = ($request->has('reference') && !empty($request->get('reference'))) ? $request->get('reference') : '';
                $transaction->transaction_type = 'Internal';
    
                if($transaction->save() && $from_wallet->save() && $to_wallet->save()){
                    DB::commit();
                    $created = true;
                    NotificationController::new($request->get('userid'), "You have successfully transferred RM" . $request->get('amount') ." from " . $from_wallet->wallet_type ." to " . $to_wallet->wallet_type ." (Internal Transaction).");
                }else{
                    DB::rollback();
                    $created = false;
                }
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

    public function newExternal(Request $request)
    {
        DB::beginTransaction();
        $created = false;
        $error = "";
        try {
            //wallet balance update
            $from_wallet = Wallet::find($request->get('walletid'));

            if($from_wallet->balance < $request->get('amount')){
                $error = "Insufficient Balance";
            }else{

                $from_wallet->balance = $from_wallet->balance - (float) $request->get('amount');

                if($request->get('to_account_type') == "Linked PayPro Wallet"){
                    $to_wallet = Wallet::find($request->get('to_account'));
                    if($to_wallet == null){
                        $error = "No wallet found";
                        return response()->json([
                            'status' => false,
                            'created' => $created,
                            'error' => $error,
                        ]);
                    }
                    $to_wallet->balance = $to_wallet->balance + (float) $request->get('amount');
                }
    
                //new transaction
                $transaction = new Transaction;
                $transaction->userid = $request->get('userid');
                $transaction->walletid = $request->get('walletid');
                $transaction->wallet_type = $from_wallet->wallet_type;
                $transaction->amount = $request->get('amount');
                $transaction->to_account = $request->get('to_account');
                $transaction->to_wallet_type = $request->get('to_account_type');
                $transaction->reference = ($request->has('reference') && !empty($request->get('reference'))) ? $request->get('reference') : '';
                $transaction->transaction_type = 'External';
    
                if($request->get('to_account_type') == "Linked PayPro Wallet"){
                    if($transaction->save() && $from_wallet->save() && $to_wallet->save()){
                        DB::commit();
                        $created = true;
                        NotificationController::new($request->get('userid'), "You have successfully transferred RM" . $request->get('amount') ." from " . $from_wallet->wallet_type ." to " . $to_wallet->wallet_type ." (External Transaction).");
                    }else{
                        DB::rollback();
                        $created = false;
                    }
                }else{
                    if($transaction->save() && $from_wallet->save()){
                        DB::commit();
                        $created = true;
                        NotificationController::new($request->get('userid'), "You have successfully transferred RM" . $request->get('amount') ." from " . $from_wallet->wallet_type ." to " .$request->get('to_account') ." (External Transaction).");
                    }else{
                        DB::rollback();
                        $created = false;
                    }
                }
            }

           
        } catch (Exception $e) {
           $error = $e;
        }
        
        return response()->json([
            'status' => true,
            'created' => $created,
            'error' => $error,
        ]);
    }

    public function getTransactions(Request $request){
        $walletID = ($request->has('walletid') && !empty($request->get('walletid'))) ? $request->get('walletid') : '';
        $error = "";
        try {
            $trx = Transaction::orderBy('id', 'desc')->where('walletid', $walletID)->orWhere(function ($query) use ($walletID) {
                $query->where('to_account', $walletID)
                      ->where('to_wallet_type', 'Linked PayPro Wallet');
            })->orWhere(function ($query) use ($walletID) {
                $query->where('to_account', $walletID)
                      ->where('transaction_type', 'Internal');
            })->get();
            foreach ($trx as $t) {
                $formattedDate = Carbon::parse($t->created_at)->format('Y-m-d');
                // $t->created_at = $formattedDate;
                $t->date = date("Y-m-d H:i:s", strtotime($t->created_at));
                $t->amount = number_format((float)$t->amount, 2, '.', '');
            }
        }catch (Exception $e) {
            $error = $e;
        }
        return response()->json([
            'status' => true,
            'data' => $trx,
            'error' => $error,
        ]);
    }

    public function biometricPayment(Request $request){
        $status = false;
        $error = "";
        $PayerEmail = ($request->has('payeremail') && !empty($request->get('payeremail'))) ? $request->get('payeremail') : '';
        $PayeeEmail = ($request->has('payeeemail') && !empty($request->get('payeeemail'))) ? $request->get('payeeemail') : '';

        DB::beginTransaction();
        try{
            $payerWallet = BiometricController::getPayerWallet($PayerEmail);
            $payeeId = User::where('email', $PayeeEmail)->pluck('id')->first();

            if($payerWallet->preferred_wallet_id == "null" || $payerWallet->preferred_wallet_id == ""){
                $error = "User has not set its preferred wallet yet.";
            }else{
               //wallet balance update
                $from_wallet = Wallet::find($payerWallet->preferred_wallet_id);
                if($from_wallet->balance < $request->get('amount')){
                    $error = "Insufficient Balance";
                }
                else{
                    $from_wallet->balance = $from_wallet->balance - (float) $request->get('amount');

                    $to_wallet = Wallet::where('userid', $payeeId)->first();
                    $to_wallet->balance = $to_wallet->balance + (float) $request->get('amount');
                    
                    //new transaction
                    $transaction = new Transaction;
                    $transaction->userid = $payerWallet->userid;
                    $transaction->walletid = $from_wallet->id;
                    $transaction->wallet_type = $from_wallet->wallet_type;
                    $transaction->amount = $request->get('amount');
                    $transaction->to_account = $to_wallet->id;
                    $transaction->to_wallet_type = "Biometric";
                    $transaction->reference = ($request->has('reference') && !empty($request->get('reference'))) ? $request->get('reference') : '';
                    $transaction->transaction_type = 'External';
    
                    if($transaction->save() && $from_wallet->save() && $to_wallet->save()){
                        DB::commit();
                        $status = true;
                        NotificationController::new($payerWallet->userid, "You have successfully paid RM" . $request->get('amount') ." from " . $from_wallet->wallet_type ." to " . $to_wallet->wallet_type ." (Biometric Payment).");
                    }else{
                        DB::rollback();
                        $created = false;
                    }
                }
            }

        } 
        catch (Exception $e) {
            $error = $e;
        }

        return response()->json([
            'status' => $status,
            'data' => $transaction,
            'error' => $error,
        ]);
    }

    public function getTransactions_merchant(Request $request){
        $status = false;
        $email = ($request->has('email') && !empty($request->get('email'))) ? $request->get('email') : '';
        $merchant_id = User::where('email', $email)->pluck('id')->first();
        $merchant_wallet = Wallet::where('userid', $merchant_id)->first();
        $error = "";
        try {
            $trx = Transaction::join('users', 'transactions.userid', '=', 'users.id')->
            orderBy('transactions.id', 'desc')->where('transactions.to_account', $merchant_wallet->id)->where('transactions.to_wallet_type', 'Biometric')
            ->select('transactions.*', 'users.name')
            ->get();
            foreach ($trx as $t) {
                $formattedDate = Carbon::parse($t->created_at)->format('Y-m-d');
                // $t->created_at = $formattedDate;
                $t->date = date("Y-m-d H:i:s", strtotime($t->created_at));
                $t->amount = number_format((float)$t->amount, 2, '.', '');
            }
            $status = true;
        }catch (Exception $e) {
            $error = $e;
        }
        return response()->json([
            'status' => $status,
            'data' => $trx,
            'error' => $error,
        ]);
    }

}
