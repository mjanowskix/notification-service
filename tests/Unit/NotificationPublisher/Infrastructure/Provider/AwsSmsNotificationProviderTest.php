<?php

namespace App\Tests\Unit\NotificationPublisher\Infrastructure\Provider;

use App\NotificationPublisher\Domain\Entity\Notification;
use App\NotificationPublisher\Domain\Exception\NotificationSendException;
use App\NotificationPublisher\Infrastructure\Provider\AwsSmsNotificationProvider;
use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Aws\CommandInterface;

class AwsSmsNotificationProviderTest extends TestCase
{
    public function testSendNotificationSuccessfully(): void
    {
        $snsClient = $this->getMockBuilder(SnsClient::class)
            ->addMethods(['publish']) // Add the method using addMethods
            ->disableOriginalConstructor()
            ->getMock();

        // Mock the SNS client's publish method to simulate successful sending
        $snsClient->expects($this->once())
            ->method('publish')
            ->willReturn($this->createMock(\Aws\Result::class)); // Returning a mock of Aws\Result

        $provider = new AwsSmsNotificationProvider($snsClient);

        $notification = new Notification(
            Uuid::v4(),
            '+1234567890', // A phone number as the recipient
            'Test SMS message',
            'sms',
            'pending'
        );

        // Ensure that no exception is thrown
        $provider->send($notification);

        $this->assertTrue(true); // If no exception is thrown, the test passes
    }

    public function testSendNotificationFails(): void
    {
        $snsClient = $this->getMockBuilder(SnsClient::class)
            ->addMethods(['publish']) // Add the method using addMethods
            ->disableOriginalConstructor()
            ->getMock();

        // Mock the AwsException with a valid CommandInterface instance
        $command = $this->createMock(CommandInterface::class);
        $awsException = new AwsException('Error sending SMS', $command);

        $snsClient->expects($this->once())
            ->method('publish')
            ->willThrowException($awsException);

        $provider = new AwsSmsNotificationProvider($snsClient);

        $notification = new Notification(
            Uuid::v4(),
            '+1234567890', // A phone number as the recipient
            'Test SMS message',
            'sms',
            'pending'
        );

        // Expect a NotificationSendException to be thrown
        $this->expectException(NotificationSendException::class);
        $this->expectExceptionMessage('AWS SNS send failed: Error sending SMS');

        // Execute the send method, which should throw an exception
        $provider->send($notification);
    }
}
