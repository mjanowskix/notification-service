<?php

namespace App\Tests\Unit\NotificationPublisher\Application\Factory;

use App\NotificationPublisher\Application\Factory\NotificationProviderFactory;
use App\NotificationPublisher\Domain\Provider\NotificationProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class NotificationProviderFactoryTest extends TestCase
{
    public function testGetProvidersForChannelReturnsCorrectProviders(): void
    {
        $providerMock = $this->createMock(NotificationProviderInterface::class);
        $providerMock->method('getName')->willReturn('mock_provider');

        $parameterBag = $this->createMock(ParameterBagInterface::class);
        $parameterBag->method('get')->willReturn([
            'sms' => [
                'enabled' => true,
                'providers' => ['mock_provider'],
            ],
        ]);

        $factory = new NotificationProviderFactory(
            [$providerMock],
            $parameterBag
        );

        $providers = $factory->getProvidersForChannel('sms');

        $this->assertCount(1, $providers);
        $this->assertSame($providerMock, $providers[0]);
    }

    public function testGetProvidersForChannelThrowsExceptionIfChannelDisabled(): void
    {
        $parameterBag = $this->createMock(ParameterBagInterface::class);
        $parameterBag->method('get')->willReturn([
            'sms' => [
                'enabled' => false,
                'providers' => ['mock_provider'],
            ],
        ]);

        $factory = new NotificationProviderFactory(
            [],
            $parameterBag
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Channel 'sms' is disabled.");

        $factory->getProvidersForChannel('sms');
    }

    public function testGetProvidersForChannelThrowsExceptionIfNoProvidersAvailable(): void
    {
        $parameterBag = $this->createMock(ParameterBagInterface::class);
        $parameterBag->method('get')->willReturn([
            'sms' => [
                'enabled' => true,
                'providers' => ['non_existing_provider'],
            ],
        ]);

        $factory = new NotificationProviderFactory(
            [],
            $parameterBag
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("No providers available for channel 'sms'.");

        $factory->getProvidersForChannel('sms');
    }
}
