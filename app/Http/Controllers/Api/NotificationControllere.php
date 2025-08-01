<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationControllere extends Controller
{
    

    public function index(Request $request) {
        return response()->json([
            'unread' => $request->user()->unreadnotifications,
            'read' => $request->user()->readnotifications
        ]);
    }

    public function markAsRead(Request $request, $id) {
    
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'mwssage' => "Notificaioin marked as read"
        ]);
    }

    public function markAllRead(Request $request){
        $notifications = $request->user()->unreadnotifications;
        if (count($notifications) == 0) {
            return response()->json([
                'message' => 'You have read all notifications'
            ]);
        }
        $notifications->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }

    public function destroy(Request $request, $id) {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json([
            'Message' => 'Notification deleted successfully'
        ]);
    }
}
