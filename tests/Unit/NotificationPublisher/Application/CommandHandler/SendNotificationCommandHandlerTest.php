<?php

namespace App\Tests\Unit\NotificationPublisher\Application\CommandHandler;

use App\NotificationPublisher\Application\Command\SendNotificationCommand;
use App\NotificationPublisher\Application\CommandHandler\SendNotificationCommandHandler;
use App\NotificationPublisher\Application\Factory\NotificationProviderFactory;
use App\NotificationPublisher\Domain\Entity\Notification;
use App\NotificationPublisher\Domain\Exception\NotificationSendException;
use App\NotificationPublisher\Domain\Provider\NotificationProviderInterface;
use App\NotificationPublisher\Domain\Repository\NotificationRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SendNotificationCommandHandlerTest extends TestCase
{
    public function testHandleSendsNotificationSuccessfully(): void
    {
        // Mock dependencies
        $notificationProvider = $this->createMock(NotificationProviderInterface::class);
        $providerFactory = $this->createMock(NotificationProviderFactory::class);
        $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        // Simulate successful sending
        $notificationProvider->expects($this->once())
            ->method('send');

        $providerFactory->expects($this->once())
            ->method('getProvidersForChannel')
            ->with('push')
            ->willReturn([$notificationProvider]);

        $notificationRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Notification::class));

        $commandHandler = new SendNotificationCommandHandler(
            $providerFactory,
            $notificationRepository,
            $logger
        );

        $command = new SendNotificationCommand(
            'Test content',
            'recipient_token',
            'push',
            null
        );

        // Execute the command handler
        $commandHandler->__invoke($command);
    }

    public function testHandleFailsWhenAllProvidersFail(): void
    {
        // Mock dependencies
        $notificationProvider = $this->createMock(NotificationProviderInterface::class);
        $providerFactory = $this->createMock(NotificationProviderFactory::class);
        $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        // Simulate failure when sending notification
        $notificationProvider->expects($this->once())
            ->method('send')
            ->willThrowException(new NotificationSendException('Provider failed'));

        $providerFactory->expects($this->once())
            ->method('getProvidersForChannel')
            ->with('push')
            ->willReturn([$notificationProvider]);

        $notificationRepository->expects($this->never())
            ->method('save');

        $commandHandler = new SendNotificationCommandHandler(
            $providerFactory,
            $notificationRepository,
            $logger
        );

        $command = new SendNotificationCommand(
            'Test content',
            'recipient_token',
            'push',
            null
        );

        // Expect a NotificationSendException to be thrown
        $this->expectException(NotificationSendException::class);
        $this->expectExceptionMessage('All providers failed to send the notification.');

        // Execute the command handler
        $commandHandler->__invoke($command);
    }

    public function testHandleRespectsNotificationLimit(): void
    {
        // Mock dependencies
        $notificationProvider = $this->createMock(NotificationProviderInterface::class);
        $providerFactory = $this->createMock(NotificationProviderFactory::class);
        $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $providerFactory->expects($this->never())
            ->method('getProvidersForChannel');

        $notificationRepository->expects($this->once())
            ->method('countNotificationsSentSince')
            ->willReturn(300);  // Simulate that the limit has been reached

        $notificationRepository->expects($this->never())
            ->method('save');

        $logger->expects($this->once())
            ->method('info')
            ->with('Notification limit reached.');

        $commandHandler = new SendNotificationCommandHandler(
            $providerFactory,
            $notificationRepository,
            $logger
        );

        $command = new SendNotificationCommand(
            'Test content',
            'recipient_token',
            'push',
            300  // Limit of 300 notifications per hour
        );

        // Execute the command handler
        $commandHandler->__invoke($command);
    }

    public function testHandleSkipsNotificationLimitWhenNotSet(): void
    {
        // Mock dependencies
        $notificationProvider = $this->createMock(NotificationProviderInterface::class);
        $providerFactory = $this->createMock(NotificationProviderFactory::class);
        $notificationRepository = $this->createMock(NotificationRepositoryInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        // Simulate successful sending
        $notificationProvider->expects($this->once())
            ->method('send');

        $providerFactory->expects($this->once())
            ->method('getProvidersForChannel')
            ->with('push')
            ->willReturn([$notificationProvider]);

        $notificationRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Notification::class));

        $commandHandler = new SendNotificationCommandHandler(
            $providerFactory,
            $notificationRepository,
            $logger
        );

        $command = new SendNotificationCommand(
            'Test content',
            'recipient_token',
            'push',
            null  // No limit set
        );

        // Execute the command handler
        $commandHandler->__invoke($command);
    }
}
