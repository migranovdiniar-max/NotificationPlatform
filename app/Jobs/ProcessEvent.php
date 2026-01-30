<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

        $webhookUrl = data_get($event->payload, 'webhook_url');

        $channel = $webhookUrl ? 'webhook' : 'log';
        $recipient = $webhookUrl ? $webhookUrl : 'system';

        // ключ идемпотентности (защита от дублей)
        $dedupeKey = implode(':', [$event->id, $channel, $recipient]);

        // создаём или берём существующую notification
        $notification = Notification::firstOrCreate(
            ['dedupe_key' => $dedupeKey],
            [
                'event_id' => $event->id,
                'channel' => $channel,
                'recipient' => $recipient,
                'status' => 'pending',
            ]
        );

        // если реально создали новую — отправляем в очередь на отправку
        if ($notification->wasRecentlyCreated) {
            SendNotification::dispatch($notification->id);
        }

        logger()->info('Created notification for event', [
            'event_id' => $event->id,
            'type' => $event->type,
            'channel' => $channel,
            'recipient' => $recipient,
            'dedupe' => $notification->wasRecentlyCreated ? 'new' : 'existing',
        ]);
    }
}
