<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Transaction;
use DB;
use Carbon\Carbon;

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
            }else{
                DB::rollback();
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

    public function newExternal(Request $request)
    {
        DB::beginTransaction();
        $created = false;
        $error = "";
        try {
            //wallet balance update
            $from_wallet = Wallet::find($request->get('walletid'));
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
                }else{
                    DB::rollback();
                    $created = false;
                }
            }else{
                if($transaction->save() && $from_wallet->save()){
                    DB::commit();
                    $created = true;
                }else{
                    DB::rollback();
                    $created = false;
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
}
