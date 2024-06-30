<?php

namespace App\Http\Controllers;

use App\Models\UserAlert;
use Illuminate\Http\Request;

class UserAlertController extends Controller
{
    public function getUnreadAlerts()
    {
        $user = auth()->user();
        $userAlerts = $user->userAlerts()->where('read', false)->get();

        return response()->json(['userAlerts' => $userAlerts]);
    }

    public function markAlertsAsRead()
    {
        $user = auth()->user();
        $user->userAlerts()->update(['read' => true]);

        return response()->json(['message' => 'User alerts marked as read']);
    }

    public function getUnreadAlertsCount()
    {
        $user = auth()->user();
        $unreadCount = $user->userAlerts()->where('read', false)->count();

        return response()->json(['unreadCount' => $unreadCount]);
    }
}

