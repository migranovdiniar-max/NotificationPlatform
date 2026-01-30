<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Notifications\Channels\ChannelResolver;
use App\Notifications\Channels\NotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

    public function handle(ChannelResolver $resolver): void
    {
        $n = Notification::with('event')->findOrFail($this->notificationId);

        $n->increment('attempts');

        if ($n->recipient === 'fail') {
            throw new \RuntimeException('Simulated sending failure');
        }

        $channelClass = $resolver->resolve($n->channel);

        /** @var NotificationChannel $channel */
        $channel = app($channelClass);

        $channel->send($n);

        $n->update([
            'status'     => 'sent',
            'sent_at'    => now(),
            'last_error' => null,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Notification::where('id', $this->notificationId)->update([
            'status'     => 'failed',
            'last_error' => $e->getMessage(),
        ]);
    }
}
