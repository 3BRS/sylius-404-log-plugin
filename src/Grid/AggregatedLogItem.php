<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\Grid;

class AggregatedLogItem
{
    public string $urlDomain;

    public string $urlSlug;

    public int $count;

    public string $lastOccurrence;

    public string $firstOccurrence;

    /**
     * @param array{
     *     urlDomain: string,
     *     urlSlug: string,
     *     logCount: int,
     *     lastOccurrence: string,
     *     firstOccurrence: string
     * } $data
     */
    public function __construct(array $data)
    {
        $this->urlDomain = $data['urlDomain'];
        $this->urlSlug = $data['urlSlug'];
        $this->count = (int) $data['logCount'];
        $this->lastOccurrence = $data['lastOccurrence'];
        $this->firstOccurrence = $data['firstOccurrence'];
    }

    public function getUrlDomain(): string
    {
        return $this->urlDomain;
    }

    public function getUrlSlug(): string
    {
        return $this->urlSlug;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getLastOccurrence(): string
    {
        return $this->lastOccurrence;
    }

    public function getFirstOccurrence(): string
    {
        return $this->firstOccurrence;
    }
}
