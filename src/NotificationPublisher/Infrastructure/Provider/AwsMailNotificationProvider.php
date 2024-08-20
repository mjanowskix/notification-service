<?php

namespace App\NotificationPublisher\Infrastructure\Provider;

use App\NotificationPublisher\Domain\Entity\Notification;
use App\NotificationPublisher\Domain\Exception\NotificationSendException;
use App\NotificationPublisher\Domain\Provider\NotificationProviderInterface;
use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

readonly class AwsMailNotificationProvider implements NotificationProviderInterface
{
    public function __construct(private SesClient $sesClient)
    {}

    public function send(Notification $notification): void
    {
        try {
            $this->sesClient->sendEmail([
                'Destination' => [
                    'ToAddresses' => [$notification->getRecipient()],
                ],
                'Message' => [
                    'Body' => [
                        'Text' => [
                            'Charset' => 'UTF-8',
                            'Data' => $notification->getContent(),
                        ],
                    ],
                    'Subject' => [
                        'Charset' => 'UTF-8',
                        'Data' => 'Title of notification', // Title of the notification
                    ],
                ],
                'Source' => 'notifications@example.com', // Sender email address
            ]);

            // Optional: Log the message ID or handle the result
        } catch (AwsException $e) {
            // Handle the error appropriately
            throw new NotificationSendException('AWS SES send failed: ' . $e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'aws_ses';
    }
}
