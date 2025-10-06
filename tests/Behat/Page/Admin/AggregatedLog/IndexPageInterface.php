<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\Sylius404LogPlugin\Behat\Page\Admin\AggregatedLog;

use FriendsOfBehat\PageObjectExtension\Page\PageInterface;

interface IndexPageInterface extends PageInterface
{
    public function countItems(): int;

    public function hasAggregatedLogForUrl(string $domain, string $urlSlug): bool;

    public function getOccurrenceCount(string $domain, string $urlSlug): ?int;

    public function filterByDomain(string $domain): void;

    public function filterByUrlPath(string $urlPath): void;

    public function filterByMinCount(int $minCount): void;

    public function filterByMaxCount(int $maxCount): void;

    public function deleteLogsFor(string $domain, string $urlSlug): void;

    public function clickDetails(string $domain, string $urlSlug): void;
}
