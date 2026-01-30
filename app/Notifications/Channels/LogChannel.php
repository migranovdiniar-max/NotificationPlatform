<?php

namespace App\Notifications\Channels;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class LogChannel implements NotificationChannel
{
    public function send(Notification $notification): void
    {
        Log::info('LogChannel: notification sent', [
            'notification_id' => $notification->id,
            'event_id'        => $notification->event_id,
            'recipient'       => $notification->recipient,
            'type'            => optional($notification->event)->type,
            'payload'         => optional($notification->event)->payload,
        ]);
    }
}
