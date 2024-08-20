<?php

namespace App\NotificationPublisher\Infrastructure\Persistence;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\NotificationPublisher\Domain\Entity\Notification;
use App\NotificationPublisher\Domain\Repository\NotificationRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Doctrine implementation of the NotificationRepositoryInterface.
 */
class DoctrineNotificationRepository implements NotificationRepositoryInterface
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Notification::class);
    }

    public function save(Notification $notification): void
    {
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    public function findById(string $id): ?Notification
    {
        return $this->repository->find($id);
    }

    public function findByRecipient(string $recipient): array
    {
        return $this->repository->findBy(['recipient' => $recipient]);
    }

    public function findByChannel(string $channel): array
    {
        return $this->repository->findBy(['channel' => $channel]);
    }

    public function countNotificationsSentSince(string $recipient, DateTime $since): int
    {
        $qb = $this->entityManager->createQueryBuilder();

        return (int) $qb->select('count(n.id)')
            ->from(Notification::class, 'n')
            ->where('n.recipient = :recipient')
            ->andWhere('n.sentAt >= :since')
            ->setParameter('recipient', $recipient)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
