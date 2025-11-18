<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Jobs\SendWebPushNotification;

class WebPushChannel
{
    /**
     * Send the given notification.
     * We'll dispatch a job that does the actual WebPush sending to avoid blocking.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (! method_exists($notification, 'toWebPush')) {
            return;
        }

        $payload = $notification->toWebPush($notifiable);

        // Ensure payload has minimal fields
        $payload = array_merge([
            'title' => $payload['title'] ?? 'Notification',
            'body' => $payload['body'] ?? '',
            'url' => $payload['url'] ?? null,
            'icon' => $payload['icon'] ?? null,
            'data' => $payload['data'] ?? [],
        ], $payload);

        // Dispatch a job to send the web push to this notifiable
        SendWebPushNotification::dispatch($notifiable->id, $payload, get_class($notifiable));
    }
}
