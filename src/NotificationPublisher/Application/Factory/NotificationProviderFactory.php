<?php

namespace App\NotificationPublisher\Application\Factory;

use App\NotificationPublisher\Domain\Provider\NotificationProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class NotificationProviderFactory
{
    private iterable $providers;
    private array $channelsConfig;

    public function __construct(
        #[TaggedIterator('app.notification_provider')] iterable $providers,
        ParameterBagInterface $params
    ) {
        $this->providers = $providers;
        $this->channelsConfig = $params->get('notification_channels');
    }

    /**
     * Returns a list of providers for the specified channel.
     *
     * @param string $channel
     * @return NotificationProviderInterface[]
     * @throws \InvalidArgumentException
     */
    public function getProvidersForChannel(string $channel): array
    {
        if (!isset($this->channelsConfig[$channel])) {
            throw new \InvalidArgumentException("Channel '{$channel}' not configured.");
        }

        if (!$this->channelsConfig[$channel]['enabled']) {
            throw new \RuntimeException("Channel '{$channel}' is disabled.");
        }

        $configuredProviders = $this->channelsConfig[$channel]['providers'];
        $availableProviders = [];

        foreach ($this->providers as $provider) {
            if (in_array($provider->getName(), $configuredProviders)) {
                $availableProviders[] = $provider;
            }
        }

        if (empty($availableProviders)) {
            throw new \RuntimeException("No providers available for channel '{$channel}'.");
        }

        return $availableProviders;
    }
}

