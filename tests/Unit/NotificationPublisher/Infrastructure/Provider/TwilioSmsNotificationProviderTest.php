<?php

namespace App\Tests\Unit\NotificationPublisher\Infrastructure\Provider;

use App\NotificationPublisher\Domain\Entity\Notification;
use App\NotificationPublisher\Domain\Exception\NotificationSendException;
use App\NotificationPublisher\Infrastructure\Provider\TwilioSmsNotificationProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Symfony\Component\Uid\Uuid;

class TwilioSmsNotificationProviderTest extends TestCase
{
    public function testSendNotificationSuccessfully(): void
    {
        $parameterBag = $this->createMock(ParameterBagInterface::class);

        // Mocking the Twilio Client and messages property
        $twilioClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $messagesMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['create'])
            ->getMock();

        // Properly assign the messages mock to the Twilio client
        $twilioClient->expects($this->any())
            ->method('__get')
            ->with('messages')
            ->willReturn($messagesMock);

        $messagesMock->expects($this->once())
            ->method('create')
            ->willReturn(null);

        $parameterBag->method('get')
            ->willReturnMap([
                ['twilio_sid', 'fake_sid'],
                ['twilio_auth_token', 'fake_auth_token'],
                ['twilio_from_number', '+1234567890'],
            ]);

        // Create the provider with the mocked dependencies
        $provider = new TwilioSmsNotificationProvider($twilioClient, $parameterBag);

        $notification = new Notification(
            Uuid::v4(),
            '+9876543210',
            'Test SMS message',
            'sms',
            'pending'
        );

        // Ensure that no exception is thrown
        $provider->send($notification);

        $this->assertTrue(true);
    }

    public function testSendNotificationFails(): void
    {
        $parameterBag = $this->createMock(ParameterBagInterface::class);

        // Mocking the Twilio Client and messages property
        $twilioClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $messagesMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['create'])
            ->getMock();

        // Properly assign the messages mock to the Twilio client
        $twilioClient->expects($this->any())
            ->method('__get')
            ->with('messages')
            ->willReturn($messagesMock);

        // Simulate the TwilioException being thrown by the create method
        $messagesMock->expects($this->once())
            ->method('create')
            ->willThrowException(new TwilioException('Error sending SMS'));

        $parameterBag->method('get')
            ->willReturnMap([
                ['twilio_sid', 'fake_sid'],
                ['twilio_auth_token', 'fake_auth_token'],
                ['twilio_from_number', '+1234567890'],
            ]);

        // Create the provider with the mocked dependencies
        $provider = new TwilioSmsNotificationProvider($twilioClient, $parameterBag);

        $notification = new Notification(
            Uuid::v4(),
            '+9876543210',
            'Test SMS message',
            'sms',
            'pending'
        );

        // Expect a NotificationSendException to be thrown
        $this->expectException(NotificationSendException::class);
        $this->expectExceptionMessage('Twilio SMS send failed: Error sending SMS');

        // Execute the send method, which should throw an exception
        $provider->send($notification);
    }
}

