<?php

namespace App\Notifications\Channels;

use App\Models\Notification;
use Illuminate\Support\Facades\Http;

class WebhookChannel
{
    public function send(Notification $n): void
    {
        $event = $n->event;

        $payload = [
            'notification_id' => $n->id,
            'event_id' => $event->id,
            'type' => $event->type,
            'source' => $event->source,
            'occurred_at' => optional($event->occurred_at)->toISOString(),
            'payload' => $event->payload,
        ];

        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $secret = (string) config('webhooks.secret');
        $signature = hash_hmac('sha256', $body, $secret);

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::connectTimeout((int) config('webhooks.connect_timeout'))
            ->timeout((int) config('webhooks.timeout'))
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-Webhook-Signature' => $signature,
                'X-Event-Type' => $event->type,
            ])
            ->withBody($body, 'application/json')
            ->post($n->recipient); 

        if (!$response->successful()) {
            $status = $response->status();
            $respBody = mb_substr((string) $response->body(), 0, 500);
            throw new \RuntimeException("Webhook failed: HTTP {$status}, body: {$respBody}");
        }
    }
}
