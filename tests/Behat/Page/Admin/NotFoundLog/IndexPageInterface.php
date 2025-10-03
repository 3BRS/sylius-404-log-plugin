<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\Sylius404LogPlugin\Behat\Page\Admin\NotFoundLog;

use Sylius\Behat\Page\Admin\Crud\IndexPageInterface as BaseIndexPageInterface;

interface IndexPageInterface extends BaseIndexPageInterface
{
    public function countItems(): int;

    public function hasLogForUrl(string $url): bool;

    public function hasLogForDomain(string $domain): bool;
}