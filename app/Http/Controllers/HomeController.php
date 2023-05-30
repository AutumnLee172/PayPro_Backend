<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Notification;
use Carbon\Carbon;

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

    public function getChartData(Request $request){
        $userid = $request->get('userid');
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        $trx = Transaction::where('userid',$userid)->where('transaction_type','External') ->whereBetween('created_at', [$startDate, $endDate])->selectRaw("UNIX_TIMESTAMP(DATE(created_at)) * 1000 as days")->selectRaw('sum(amount) as sum')->groupBy('days')->get();
        
        // $formating = $trx->map(function ($item) {
        //     return [
        //         $item->days,
        //         $item->sum
        //     ];
        // });

        // $result = $formating->toJson();
        
        return response()->json([
            'status' => true,
            'data' => $trx
        ]);
    }
}
