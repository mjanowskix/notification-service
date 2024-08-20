<?php

namespace App\NotificationPublisher\Application\CommandHandler;

use App\NotificationPublisher\Application\Command\SendNotificationCommand;
use App\NotificationPublisher\Application\Factory\NotificationProviderFactory;
use App\NotificationPublisher\Domain\Repository\NotificationRepositoryInterface;
use App\NotificationPublisher\Domain\Entity\Notification;
use App\NotificationPublisher\Domain\Exception\NotificationSendException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

/**
 * Command handler for sending notifications.
 */
#[AsMessageHandler]
final readonly class SendNotificationCommandHandler
{
    public function __construct(
        private NotificationProviderFactory     $providerFactory,
        private NotificationRepositoryInterface $notificationRepository,
        private LoggerInterface                 $logger
    ) {}

    public function __invoke(SendNotificationCommand $command): void
    {
        $recipient = $command->getRecipient();

        // Check if the recipient has reached the notification limit
        if ($this->canSendNotification($command)) {

            // Get the appropriate providers for the specified channel
            $providers = $this->providerFactory->getProvidersForChannel($command->getChannel());

            // Create a new notification entity
            $notification = new Notification(
                Uuid::v4(),
                $recipient,
                $command->getContent(),
                $command->getChannel(),
                'pending'
            );


            foreach ($providers as $provider) {
                try {
                    // Attempt to send the notification using the current provider
                    $provider->send($notification);
                    $notification->markAsSent();
                    break; // Exit the loop if sending was successful
                } catch (NotificationSendException $e) {
                    // Log failure and try the next provider
                    $this->logger->error(
                        'Failed to send notification.',
                        [
                            'provider' => $provider->getName(),
                            'recipient' => $recipient,
                            'channel' => $command->getChannel(),
                            'error' => $e->getMessage()
                        ]
                    );
                }
            }

            if ($notification->getStatus() !== 'sent') {
                $notification->markAsFailed();
                throw new NotificationSendException('All providers failed to send the notification.');
            }

            // Save the notification to the repository
            $this->notificationRepository->save($notification);

        } else {
            // Log that the notification limit has been reached
            $this->logger->info(
                'Notification limit reached.',
                [
                    'recipient' => $recipient,
                    'limit' => $command->getLimitPerHour()
                ]
            );
        }
    }

    private function canSendNotification(SendNotificationCommand $command): bool
    {
        if ($command->getLimitPerHour() === null) {
            return true;
        }
        $oneHourAgo = new \DateTime('-1 hour');
        $sentCount = $this->notificationRepository->countNotificationsSentSince($command->getRecipient(), $oneHourAgo);
        return $sentCount < $command->getLimitPerHour();
    }
}


