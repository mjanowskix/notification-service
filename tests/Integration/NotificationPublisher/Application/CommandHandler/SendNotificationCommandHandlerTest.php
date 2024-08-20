<?php

namespace App\Tests\Integration\NotificationPublisher\Application\CommandHandler;

use App\NotificationPublisher\Application\Command\SendNotificationCommand;
use App\NotificationPublisher\Application\CommandHandler\SendNotificationCommandHandler;
use App\NotificationPublisher\Application\Factory\NotificationProviderFactory;
use App\NotificationPublisher\Domain\Provider\NotificationProviderInterface;
use App\NotificationPublisher\Domain\Repository\NotificationRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SendNotificationCommandHandlerTest extends TestCase
{
    private SendNotificationCommandHandler $handler;
    private NotificationProviderInterface $mockProvider;
    private NotificationRepositoryInterface $mockRepository;

    protected function setUp(): void
    {
        $this->mockProvider = $this->createMock(NotificationProviderInterface::class);
        $this->mockProvider->method('getName')->willReturn('twilio');

        // Mock the repository to simulate saving notifications
        $this->mockRepository = $this->createMock(NotificationRepositoryInterface::class);
        $this->mockRepository->expects($this->never())->method('save');

        // Mock the NotificationProviderFactory to return our mock provider
        $mockFactory = $this->createMock(NotificationProviderFactory::class);
        $mockFactory->method('getProvidersForChannel')->willReturn([$this->mockProvider]);

        $mockLogger = $this->createMock(LoggerInterface::class);

        $this->handler = new SendNotificationCommandHandler(
            $mockFactory,
            $this->mockRepository,
            $mockLogger
        );
    }

    public function testHandleNotificationFailure(): void
    {
        $this->mockProvider->expects($this->once())
            ->method('send')
            ->willThrowException(new \Exception('Provider failed'));

        $command = new SendNotificationCommand(
            'Test message that should fail',
            '+1234567890',
            'sms',
            5
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Provider failed');

        // Execute the handler, expecting an exception
        $this->handler->__invoke($command);

        // Ensure save is never called
        $this->mockRepository->expects($this->never())->method('save');
    }
}
