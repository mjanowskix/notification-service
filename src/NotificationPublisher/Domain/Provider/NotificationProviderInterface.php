<?php

namespace App\NotificationPublisher\Domain\Provider;

use App\NotificationPublisher\Domain\Entity\Notification;

interface NotificationProviderInterface
{
    /**
     * Sends a notification.
     *
     * @param Notification $notification
     */
    public function send(Notification $notification): void;

    /**
     * Returns the name of the provider.
     *
     * @return string
     */
    public function getName(): string;
}
