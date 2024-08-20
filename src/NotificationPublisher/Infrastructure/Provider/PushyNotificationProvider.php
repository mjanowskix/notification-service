<?php

namespace App\NotificationPublisher\Infrastructure\Provider;

use App\NotificationPublisher\Domain\Entity\Notification;
use App\NotificationPublisher\Domain\Exception\NotificationSendException;
use App\NotificationPublisher\Domain\Provider\NotificationProviderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class PushyNotificationProvider implements NotificationProviderInterface
{
    public function __construct(
        private HttpClientInterface   $client,
        private ParameterBagInterface $parameterBag,
    )
    {}

    public function send(Notification $notification): void
    {
        $apiKey = $this->parameterBag->get('pushy_api_key');
        try {
            $response = $this->client->request('POST', 'https://api.pushy.me/push?api_key=' . $apiKey, [
                'json' => [
                    'to' => $notification->getRecipient(), // This should be the device token
                    'data' => [
                        'message' => $notification->getContent(),
                    ],
                    'notification' => [
                        'badge' => 1,
                        'title' => 'New Notification',
                        'body' => $notification->getContent(),
                    ],
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $responseData = $response->toArray();

            if ($statusCode !== 200 || !$responseData['success']) {
                throw new \RuntimeException('Pushy send failed: ' . $responseData['error']);
            }
        } catch (\Throwable $e) {
            // Handle exceptions from the HTTP client
            throw new NotificationSendException('Pushy send failed: ' . $e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'pushy';
    }
}
