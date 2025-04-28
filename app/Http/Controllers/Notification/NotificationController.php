<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Helpers\Helper;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Helper::userCheck()['user_type'];

        $user= $user::find(Helper::getAuthenticatedUser()->id);
        $notifications = $user->notifications;
        return view('notification.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $user = Helper::userCheck()['user_type'];

        $notification = $user::find(Helper::getAuthenticatedUser()->id)->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }


        return redirect()->back(); // Redirect to the previous page or any URL you want
    }

    public function readAll()
    {
        $user = Helper::userCheck()['user_type'];

        // Get the authenticated user
        $user = $user::find(Helper::getAuthenticatedUser()->id);

        // Mark all unread notifications as read
        $user->unreadNotifications->markAsRead();

        // Redirect back to the notifications page or any desired route
        return redirect()->back()->with('success', 'All notifications have been marked as read.');
    }
}
