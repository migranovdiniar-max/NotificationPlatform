<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendNotification;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::query()
            ->with('event')
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    public function show(Notification $notification)
    {
        $notification->load('event');

        return response()->json($notification);
    }

    public function retry(Notification $notification)
    {
        if ($notification->status !== 'failed') {
            return response()->json([
                'message' => 'Retry is allowed only for failed notifications.',
                'status' => $notification->status,
            ], 422);
        }

        $notification->update([
            'status' => 'pending',
            'last_error' => null,
            'attempts' => 0,
            'sent_at' => null,
        ]);

        SendNotification::dispatch($notification->id);

        return response()->json([
            'id' => $notification->id,
            'status' => 'queued',
        ]);
    }
}
