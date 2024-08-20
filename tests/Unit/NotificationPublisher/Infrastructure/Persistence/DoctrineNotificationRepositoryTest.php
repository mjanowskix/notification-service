<?php

namespace App\Tests\Unit\NotificationPublisher\Infrastructure\Persistence;

use App\NotificationPublisher\Domain\Entity\Notification;
use App\NotificationPublisher\Infrastructure\Persistence\DoctrineNotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class DoctrineNotificationRepositoryTest extends TestCase
{
    public function testSaveNotification(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(EntityRepository::class);

        $entityManager->method('getRepository')
            ->willReturn($repository);

        $notification = new Notification(
            Uuid::v4(),
            'recipient_token',
            'Test content',
            'push',
            'pending'
        );

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($notification);

        $entityManager->expects($this->once())
            ->method('flush');

        $repository = new DoctrineNotificationRepository($entityManager);
        $repository->save($notification);
    }
}
