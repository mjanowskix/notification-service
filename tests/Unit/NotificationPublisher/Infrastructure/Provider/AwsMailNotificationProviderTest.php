<?php

namespace App\Tests\Unit\NotificationPublisher\Infrastructure\Provider;

use App\NotificationPublisher\Domain\Entity\Notification;
use App\NotificationPublisher\Domain\Exception\NotificationSendException;
use App\NotificationPublisher\Infrastructure\Provider\AwsMailNotificationProvider;
use Aws\Ses\SesClient;
use Aws\Exception\AwsException;
use Aws\CommandInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class AwsMailNotificationProviderTest extends TestCase
{
    public function testSendNotificationSuccessfully(): void
    {
        $sesClient = $this->getMockBuilder(SesClient::class)
            ->addMethods(['sendEmail'])
            ->disableOriginalConstructor()
            ->getMock();

        $sesClient->expects($this->once())
            ->method('sendEmail')
            ->willReturn($this->createMock(\Aws\Result::class));

        $provider = new AwsMailNotificationProvider($sesClient);

        $notification = new Notification(
            Uuid::v4(),
            'recipient@example.com',
            'Test message',
            'email',
            'pending'
        );

        $provider->send($notification);

        $this->assertTrue(true);
    }

    public function testSendNotificationFails(): void
    {
        $sesClient = $this->getMockBuilder(SesClient::class)
            ->addMethods(['sendEmail'])
            ->disableOriginalConstructor()
            ->getMock();

        // Mock the AwsException with a valid CommandInterface instance
        $command = $this->createMock(CommandInterface::class);
        $awsException = new AwsException('Error sending email', $command);

        $sesClient->expects($this->once())
            ->method('sendEmail')
            ->willThrowException($awsException);

        $provider = new AwsMailNotificationProvider($sesClient);

        $notification = new Notification(
            Uuid::v4(),
            'recipient@example.com',
            'Test message',
            'email',
            'pending'
        );

        $this->expectException(NotificationSendException::class);
        $this->expectExceptionMessage('AWS SES send failed: Error sending email');

        $provider->send($notification);
    }
}
