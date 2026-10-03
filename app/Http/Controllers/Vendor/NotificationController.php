<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);
        return view('vendor.notifications.index', compact('notifications'));
    }

    public function markRead($id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        $url = $notification->data['url'] ?? route('vendor.notifications.index');
        return redirect($url);
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    }
}
