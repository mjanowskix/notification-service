<?php

namespace App\NotificationPublisher\Domain\Repository;

use App\NotificationPublisher\Domain\Entity\Notification;
use DateTime;

interface NotificationRepositoryInterface
{
    /**
     * Saves a notification to the database.
     *
     * @param Notification $notification
     */
    public function save(Notification $notification): void;

    /**
     * Finds a notification by its identifier.
     *
     * @param string $id
     * @return Notification|null
     */
    public function findById(string $id): ?Notification;

    /**
     * Finds all notifications sent to a specific recipient.
     *
     * @param string $recipient
     * @return Notification[]
     */
    public function findByRecipient(string $recipient): array;

    /**
     * Finds all notifications sent through a specific channel.
     *
     * @param string $channel
     * @return Notification[]
     */
    public function findByChannel(string $channel): array;

    /**
     * Counts the number of notifications sent to a specific recipient since a given time.
     *
     * @param string $recipient
     * @param DateTime $since
     * @return int
     */
    public function countNotificationsSentSince(string $recipient, DateTime $since): int;
}
