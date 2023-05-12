<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function new($userid, $description)
    {
        try {
            $notification = new Notification;
            $notification->userid = $userid;
            $notification->description = $description;
            $notification->save();

        } catch (Throwable $e) {
          return;
        }
        
    }

    public function get(Request $request){
        $userid = $request->get('userid');
        $notifications = Notification::orderBy('created_at', 'desc')->where('userid',$userid)->get();
        foreach ($notifications as $n) {
            $n->date = date("Y-m-d H:i:s", strtotime($n->created_at));
        }
        return response()->json([
            'status' => true,
            'data' => $notifications
        ]);
    }
}
