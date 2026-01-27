<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\Sylius404LogPlugin\Behat\Page\Admin\AggregatedLog;

use FriendsOfBehat\PageObjectExtension\Page\PageInterface;

interface DetailsPageInterface extends PageInterface
{
    public function getTotalCount(): int;

    public function hasStatistic(string $label, string $value): bool;

    public function countIndividualLogs(): int;

    public function hasChartData(): bool;
}
