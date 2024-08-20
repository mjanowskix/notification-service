<?php

namespace App\Tests\Unit\NotificationPublisher\Infrastructure\Provider;

use App\NotificationPublisher\Infrastructure\Provider\PushyNotificationProvider;
use App\NotificationPublisher\Domain\Entity\Notification;
use App\NotificationPublisher\Domain\Exception\NotificationSendException;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Uid\Uuid;

class PushyNotificationProviderTest extends TestCase
{
    public function testSendNotificationSuccessfully(): void
    {
        // Mock the HttpClientInterface and ResponseInterface
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $parameterBag = $this->createMock(ParameterBagInterface::class);

        // Simulate a successful response from the Pushy API
        $response->method('getStatusCode')
            ->willReturn(200);

        $response->method('toArray')
            ->willReturn(['success' => true]);

        $httpClient->method('request')
            ->willReturn($response);

        // Simulate returning the API key from ParameterBagInterface
        $parameterBag->method('get')
            ->with('pushy_api_key')
            ->willReturn('fake_api_key');

        // Initialize the provider with mocked dependencies
        $provider = new PushyNotificationProvider($httpClient, $parameterBag);

        // Create a Notification entity
        $notification = new Notification(
            Uuid::v4(),
            'recipient_token',
            'Test message',
            'push',
            'pending'
        );

        // Verify that no exception is thrown
        $provider->send($notification);

        // If no exception is thrown, the test is considered successful
        $this->assertTrue(true);
    }

    public function testSendNotificationFails(): void
    {
        // Mock the HttpClientInterface and ResponseInterface
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $parameterBag = $this->createMock(ParameterBagInterface::class);

        // Simulate a failure response from the Pushy API
        $response->method('getStatusCode')
            ->willReturn(200);

        $response->method('toArray')
            ->willReturn(['success' => false, 'error' => 'Some error']);

        $httpClient->method('request')
            ->willReturn($response);

        // Simulate returning the API key from ParameterBagInterface
        $parameterBag->method('get')
            ->with('pushy_api_key')
            ->willReturn('fake_api_key');

        // Initialize the provider with mocked dependencies
        $provider = new PushyNotificationProvider($httpClient, $parameterBag);

        // Create a Notification entity
        $notification = new Notification(
            Uuid::v4(),
            'recipient_token',
            'Test message',
            'push',
            'pending'
        );

        // Expect a NotificationSendException to be thrown
        $this->expectException(NotificationSendException::class);
        $this->expectExceptionMessage('Pushy send failed: Some error');

        // Attempt to send the notification, which should fail
        $provider->send($notification);
    }

    public function testSendNotificationThrowsExceptionOnHttpClientError(): void
    {
        // Mock the HttpClientInterface and ParameterBagInterface
        $httpClient = $this->createMock(HttpClientInterface::class);
        $parameterBag = $this->createMock(ParameterBagInterface::class);

        // Simulate an exception being thrown by the HttpClient
        $httpClient->method('request')
            ->willThrowException(new \Exception('HTTP error'));

        // Simulate returning the API key from ParameterBagInterface
        $parameterBag->method('get')
            ->with('pushy_api_key')
            ->willReturn('fake_api_key');

        // Initialize the provider with mocked dependencies
        $provider = new PushyNotificationProvider($httpClient, $parameterBag);

        // Create a Notification entity
        $notification = new Notification(
            Uuid::v4(),
            'recipient_token',
            'Test message',
            'push',
            'pending'
        );

        // Expect a NotificationSendException to be thrown
        $this->expectException(NotificationSendException::class);
        $this->expectExceptionMessage('Pushy send failed: HTTP error');

        // Attempt to send the notification, which should result in an exception
        $provider->send($notification);
    }
}
