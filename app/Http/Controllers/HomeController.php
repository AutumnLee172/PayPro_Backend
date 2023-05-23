<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Notification;

class HomeController extends Controller
{
    public function getWalletsValue(Request $request){
        $userid = $request->get('userid');
        $wallet = wallet::where('userid',$userid)->selectRaw('sum(balance) as sum, COUNT(*) as walletnumber')->first();
        return response()->json([
            'status' => true,
            'data' => $wallet
        ]);
    }

    public function getLatestActivities(Request $request){
        $userid = $request->get('userid');
        $notifications = Notification::orderBy('created_at', 'desc')->where('userid',$userid)->take(5)->get();
        foreach ($notifications as $n) {
            $n->date = date("Y-m-d H:i:s", strtotime($n->created_at));
        }
        return response()->json([
            'status' => true,
            'data' => $notifications
        ]);
    }
}
