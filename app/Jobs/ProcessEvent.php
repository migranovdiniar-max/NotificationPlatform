<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Jobs\SendNotification;

class ProcessEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $eventId;

    public function __construct(int $eventId)
    {
        $this->eventId = $eventId;
    }

    public function handle(): void
    {
        $event = Event::findOrFail($this->eventId);

        $notification = Notification::create([
            'event_id' => $event->id,
            'channel' => 'log',
            'recipient' => 'system',
            'status' => 'pending',
        ]);

        Log::info('Created notification for event', [
            'event_id' => $event->id,
            'type' => $event->type,
            'notification_id' => $notification->id,
        ]);

        SendNotification::dispatch($notification->id);
    }
}
