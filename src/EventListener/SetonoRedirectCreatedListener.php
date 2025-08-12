<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\EventListener;

use ThreeBRS\Sylius404LogPlugin\Repository\NotFoundLogRepositoryInterface;

class SetonoRedirectCreatedListener
{
    public function __construct(
        private readonly NotFoundLogRepositoryInterface $notFoundLogRepository,
    ) {
    }

    // Doctrine entity listener - entity se předává přímo jako parametr
    public function postPersist(\Setono\SyliusRedirectPlugin\Model\RedirectInterface $entity): void
    {
        $sourceUrl = $entity->getSource();
        if ($sourceUrl) {
            $channels = $entity->getChannels();
            $this->deleteMatchingNotFoundLogs($sourceUrl, $channels);
        }
    }

    /**
     * @param iterable<\Sylius\Component\Channel\Model\ChannelInterface> $channels
     */
    private function deleteMatchingNotFoundLogs(string $sourceUrl, iterable $channels): void
    {
        if ($channels instanceof \Doctrine\ORM\PersistentCollection) {
            $channelsArray = $channels->toArray();
        } else {
            $channelsArray = iterator_to_array($channels);
        }

        // If no specific channels, delete from all domains
        if (count($channelsArray) === 0) {
            $this->notFoundLogRepository->deleteByUrl($sourceUrl);

            return;
        }

        // Delete logs for specific channels/domains
        foreach ($channelsArray as $channel) {
            if (method_exists($channel, 'getHostname')) {
                $hostname = $channel->getHostname();
                if ($hostname) {
                    $this->notFoundLogRepository->deleteByUrlAndDomain($sourceUrl, $hostname);
                }
            }
        }
    }
}
