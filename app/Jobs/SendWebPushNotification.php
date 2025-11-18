<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;

class SendWebPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $notifiableId;
    public string $notifiableType;
    public array $payload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $notifiableId, array $payload, string $notifiableType = 'App\\Models\\User')
    {
        $this->notifiableId = $notifiableId;
        $this->payload = $payload;
        $this->notifiableType = $notifiableType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $vapid = [
            'VAPID' => [
                'subject' => config('app.url'),
                'publicKey' => env('WEBPUSH_VAPID_PUBLIC'),
                'privateKey' => env('WEBPUSH_VAPID_PRIVATE'),
            ],
        ];

        $webPush = new WebPush($vapid);

        // Find subscriptions for this user (we store subscriptions with user_id)
        $subscriptions = PushSubscription::where('user_id', $this->notifiableId)->get();

        foreach ($subscriptions as $sub) {
            try {
                // Build Subscription using keys array to match standard WebPush format
                $subscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'keys' => [
                        'p256dh' => $sub->public_key,
                        'auth' => $sub->auth_token,
                    ],
                ]);

                Log::info('Sending WebPush to subscription', ['sub_id' => $sub->id, 'notifiable_id' => $this->notifiableId]);

                $report = $webPush->sendOneNotification($subscription, json_encode($this->payload));

                if ($report->isSuccess()) {
                    Log::info('WebPush sent to subscription', ['id' => $sub->id]);
                } else {
                    $statusCode = $report->getResponse() ? $report->getResponse()->getStatusCode() : null;

                    // Remove subscription if not found or gone
                    if (in_array($statusCode, [404, 410])) {
                        Log::info('Removing expired push subscription', ['id' => $sub->id, 'status' => $statusCode]);
                        $sub->delete();
                    } else {
                        Log::warning('WebPush failed for subscription', ['id' => $sub->id, 'status' => $statusCode]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error sending WebPush', ['error' => $e->getMessage(), 'subscription_id' => $sub->id]);
            }
        }
    }
}
