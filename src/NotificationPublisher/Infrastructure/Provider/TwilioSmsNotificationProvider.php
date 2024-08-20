<?php

namespace App\NotificationPublisher\Infrastructure\Provider;

use App\NotificationPublisher\Domain\Entity\Notification;
use App\NotificationPublisher\Domain\Exception\NotificationSendException;
use App\NotificationPublisher\Domain\Provider\NotificationProviderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twilio\Rest\Client;

readonly class TwilioSmsNotificationProvider implements NotificationProviderInterface
{

    public function __construct(
        private Client $twilioClient,
        private ParameterBagInterface $parameterBag
    ) {}

    public function send(Notification $notification): void
    {
        try {
            $this->twilioClient->messages->create(
                $notification->getRecipient(),
                [
                    'from' => $this->parameterBag->get('twilio_from_number'),
                    'body' => $notification->getContent(),
                ]
            );

        } catch (\Throwable $e) {
            throw new NotificationSendException('Twilio SMS send failed: ' . $e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'twilio';
    }
}
