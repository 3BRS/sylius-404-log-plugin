<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\Repository;

use Doctrine\ORM\QueryBuilder;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use ThreeBRS\Sylius404LogPlugin\Entity\NotFoundLogInterface;

/**
 * @extends RepositoryInterface<NotFoundLogInterface>
 */
interface NotFoundLogRepositoryInterface extends RepositoryInterface
{
    public function createAggregatedQueryBuilder(): QueryBuilder;

    /**
     * Finds all logs for a specific domain and slug.
     *
     * @param string $domain The URL domain to filter by.
     * @param string $slug The URL slug to filter by.
     *
     * @return NotFoundLogInterface[] An array of NotFoundLog entities matching the criteria.
     */
    public function findByDomainAndSlug(string $domain, string $slug): array;

    /**
     * @return array{
     *     totalCount: int,
     *     lastOccurrence: \DateTimeInterface|null,
     *     firstOccurrence: \DateTimeInterface|null
     * }
     */
    public function getAggregatedStats(string $domain, string $slug): array;
}
