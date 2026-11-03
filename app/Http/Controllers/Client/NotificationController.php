<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('client.alerts', [
            'user' => $user,
            'notifications' => $user->notifications()->limit(30)->get(),
        ]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }

    public function updatePreferences(Request $request)
    {
        $request->user()->update([
            'notify_mail' => $request->boolean('notify_mail'),
            'notify_sms' => $request->boolean('notify_sms'),
        ]);

        return back()->with('status', 'Préférences enregistrées.');
    }
}