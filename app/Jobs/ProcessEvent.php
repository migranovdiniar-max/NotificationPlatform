<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        $event = Event::find($this->eventId);

        if (!$event) {
            Log::warning('Event not found', ['event_id' => $this->eventId]);
            return;
        }

        Log::info('Processing event', [
            'id' => $event->id,
            'type' => $event->type,
            'payload' => $event->payload,
        ]);

        sleep(3);
    }
}
