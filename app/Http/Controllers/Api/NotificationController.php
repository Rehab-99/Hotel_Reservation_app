<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function NotificationsCount(){
        $user = Auth::user();
        $unreadCount = $user->unreadNotifications->count();

        return response()->json([
            'status'=>200,
            'message'=>'This is count of unRead notifications ',
            'data'=>['count'=>$unreadCount],
        ]);
    }

    public function Notifications(){
        $user = Auth::user();
        $unreadCount = $user->unreadNotifications;

        return response()->json([
            'status'=>200,
            'message'=>'All unRead notifications ',
            'data'=>['unRead notifications'=>$unreadCount],
        ]);
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = Notification::where('id', $id)->first();

        if($notification){
            $notification->update(['read_at' => now()]);
            return response()->json([
                'status' => 200,
                'message' => 'Notification marked as read',
                'data'=>['notification'=>$notification],
            ]);
        }else {
            return response()->json([
                'status' => 404,
                'message' => 'Notification not found',
                'data'=>[],
            ]);
        }

    }
}
