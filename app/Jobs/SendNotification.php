<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $notificationId;

    public int $tries = 3;
    public int|array $backoff = [2, 5, 10];

    public function __construct(int $notificationId)
    {
        $this->notificationId = $notificationId;
    }

    public function handle(): void
    {
        $n = Notification::with('event')->findOrFail($this->notificationId);

        $n->increment('attempts');

        if ($n->recipient === 'fail') {
            throw new \RuntimeException('Simulated sending failure');
        }

        Log::info('Sending notification', [
            'notification_id' => $n->id,
            'event_id' => $n->event_id,
            'channel' => $n->channel,
            'recipient' => $n->recipient,
            'attempt' => $n->attempts,
        ]);

        $n->update([
            'status' => 'sent',
            'sent_at' => now(),
            'last_error' => null,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Notification::where('id', $this->notificationId)->update([
            'status' => 'failed',
            'last_error' => $e->getMessage(),
        ]);
    }
}
