<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use App\Models\PushSubscription;

class SendWebPush implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payload;
    public $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($payload = [], $userId = null)
    {
        $this->payload = $payload;
        $this->userId = $userId;
    }

    public function handle()
    {
        // 🔥 UTILISEZ LA CONFIGURATION LARAVEL AU LIEU DE env() DIRECTEMENT
        $vapid = [
            'VAPID' => [
                'subject' => config('webpush.vapid.subject'),
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ]
        ];

        // 🔥 VALIDATION DES CLÉS VAPID
        if (empty($vapid['VAPID']['publicKey']) || empty($vapid['VAPID']['privateKey'])) {
            logger()->error('Clés VAPID manquantes dans la configuration');
            return;
        }

        logger()->info('Configuration VAPID chargée', [
            'subject' => $vapid['VAPID']['subject'],
            'publicKey_length' => strlen($vapid['VAPID']['publicKey']),
            'privateKey_length' => strlen($vapid['VAPID']['privateKey'])
        ]);

        $webPush = new WebPush($vapid);

        // Récupérer les abonnements
        $query = PushSubscription::query();
        
        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }
        
        $subs = $query->get();

        if ($subs->isEmpty()) {
            logger()->info('Aucun abonnement WebPush trouvé');
            return;
        }

        $payloadJson = is_array($this->payload) ? json_encode($this->payload) : (string) $this->payload;

        foreach ($subs as $subscription) {
            try {
                $pushSubscription = Subscription::create([
                    'endpoint' => $subscription->endpoint,
                    'publicKey' => $subscription->public_key,
                    'authToken' => $subscription->auth_token,
                ]);
                
                $webPush->queueNotification($pushSubscription, $payloadJson);
                
            } catch (\Throwable $e) {
                logger()->error('Erreur création subscription WebPush', [
                    'error' => $e->getMessage(), 
                    'sub_id' => $subscription->id
                ]);
            }
        }

        // Envoyer les notifications
        $results = $webPush->flush();
        
        $successCount = 0;
        $failCount = 0;

        foreach ($results as $report) {
            $endpoint = $report->getRequest()->getUri();
            
            if ($report->isSuccess()) {
                $successCount++;
                logger()->info('WebPush envoyé avec succès', ['endpoint' => $endpoint]);
            } else {
                $failCount++;
                logger()->warning('WebPush échoué', [
                    'endpoint' => $endpoint,
                    'statusCode' => $report->getResponse() ? $report->getResponse()->getStatusCode() : null,
                    'reason' => $report->getReason()
                ]);
                
                // Supprimer les abonnements invalides
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $endpoint)->delete();
                    logger()->info('Abonnement expiré supprimé', ['endpoint' => $endpoint]);
                }
            }
        }

        logger()->info('Résumé WebPush', [
            'success' => $successCount,
            'failed' => $failCount,
            'total' => $subs->count()
        ]);
    }
}