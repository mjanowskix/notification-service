<?php

namespace App\NotificationPublisher\Application\Command;

/**
 * Command to send a notification.
 */
final readonly class SendNotificationCommand
{
    public function __construct(
        private string $content,
        private string $recipient,
        private string $channel,
        private ?int $limitPerHour = null
    ) {}

    public function getContent(): string
    {
        return $this->content;
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getLimitPerHour(): ?int
    {
        return $this->limitPerHour;
    }
}
