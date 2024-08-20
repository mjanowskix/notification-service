<?php

namespace App\NotificationPublisher\Infrastructure\Provider;

use App\NotificationPublisher\Domain\Entity\Notification;
use App\NotificationPublisher\Domain\Exception\NotificationSendException;
use App\NotificationPublisher\Domain\Provider\NotificationProviderInterface;
use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;

readonly class AwsSmsNotificationProvider implements NotificationProviderInterface
{
    public function __construct(private SnsClient $snsClient)
    {}

    public function send(Notification $notification): void
    {
        try {
            $this->snsClient->publish([
                'Message' => $notification->getContent(),
                'PhoneNumber' => $notification->getRecipient(), // The recipient phone number
            ]);

            // Optional: Log the message ID or handle the result
        } catch (AwsException $e) {
            // Handle the error appropriately
            throw new NotificationSendException('AWS SNS send failed: ' . $e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'aws_sns';
    }
}

