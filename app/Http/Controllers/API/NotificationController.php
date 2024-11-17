<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateNotificationSettingsRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\NotificationSettingResource;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->paginate(15);

        return NotificationResource::collection($notifications);
    }

    public function markAsRead(Request $request)
    {
        auth()->user()
            ->unreadNotifications
            ->when($request->id, function ($query) use ($request) {
                return $query->where('id', $request->id);
            })
            ->markAsRead();

        return response()->json(['message' => 'Notifications marked as read']);
    }

    public function settings()
    {
        return new NotificationSettingResource(auth()->user()->notificationSetting);
    }

    public function updateSettings(UpdateNotificationSettingsRequest $request)
    {
        $settings = auth()->user()->notificationSetting;
        $settings->update($request->validated());

        return new NotificationSettingResource($settings);
    }
} 