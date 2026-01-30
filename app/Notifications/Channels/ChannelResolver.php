<?php

namespace App\Notifications\Channels;

use InvalidArgumentException;

class ChannelResolver
{
    /** @var array<string, class-string<NotificationChannel>> */
    private array $map;

    public function __construct()
    {
        // тут подключаем каналы
        $this->map = [
            'log' => LogChannel::class,
            // 'email' => EmailChannel::class,
            // 'webhook' => WebhookChannel::class,
        ];
    }

    public function resolve(string $channel): string
    {
        $channel = strtolower(trim($channel));

        if (! isset($this->map[$channel])) {
            throw new InvalidArgumentException("Unknown notification channel: {$channel}");
        }

        return $this->map[$channel];
    }
}
