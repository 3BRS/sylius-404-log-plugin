<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\Repository;

use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use ThreeBRS\Sylius404LogPlugin\Entity\NotFoundLogInterface;

class NotFoundLogRepository extends EntityRepository implements NotFoundLogRepositoryInterface
{
    /**
     * Finds all logs for a specific domain and slug.
     *
     * @param string $domain The URL domain to filter by.
     * @param string $slug The URL slug to filter by.
     *
     * @return NotFoundLogInterface[] An array of NotFoundLog entities matching the criteria.
     */
    public function findByDomainAndSlug(string $domain, string $slug): array
    {
        // @phpstan-ignore-next-line return.type
        return $this->createQueryBuilder('nfl')
            ->where('nfl.urlDomain = :domain')
            ->andWhere('nfl.urlSlug = :slug')
            ->setParameter('domain', $domain)
            ->setParameter('slug', $slug)
            ->orderBy('nfl.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array{
     *     totalCount: int,
     *     lastOccurrence: \DateTimeInterface|null,
     *     firstOccurrence: \DateTimeInterface|null
     * }
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getAggregatedStats(string $domain, string $slug): array
    {
        // @phpstan-ignore-next-line return.type
        return $this->createQueryBuilder('nfl')
            ->select([
                'COUNT(nfl.id) as totalCount',
                'MAX(nfl.createdAt) as lastOccurrence',
                'MIN(nfl.createdAt) as firstOccurrence',
            ])
            ->where('nfl.urlDomain = :domain')
            ->andWhere('nfl.urlSlug = :slug')
            ->setParameter('domain', $domain)
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @noinspection PhpUnused used by Sylius Grid
     */
    public function createQueryBuilderForGrid(): QueryBuilder
    {
        // For standard grid, return a normal QueryBuilder
        return $this->createQueryBuilder('nfl')
            ->orderBy('nfl.createdAt', 'DESC');
    }

    /**
     * @return array<int, array{date: string, count: int}>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function getChartData(string $domain, string $slug, int $days = 30): array
    {
        $startDate = new \DateTime();
        $startDate->modify("-{$days} days");

        // Use native SQL query for better compatibility
        $connection = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM three_brs_404_not_found_log
            WHERE url_domain = :domain
            AND url_slug = :slug
            AND created_at >= :startDate
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ';

        $stmt = $connection->prepare($sql);
        $stmt->bindValue('domain', $domain);
        $stmt->bindValue('slug', $slug);
        $stmt->bindValue('startDate', $startDate->format('Y-m-d H:i:s'));

        $result = $stmt->executeQuery()->fetchAllAssociative();

        // Fill missing days with zeros
        $chartData = [];
        $currentDate = clone $startDate;
        $now = new \DateTime();

        while ($currentDate <= $now) {
            $dateString = $currentDate->format('Y-m-d');
            $found = false;

            foreach ($result as $row) {
                if ($row['date'] === $dateString) {
                    $chartData[] = [
                        'date' => $dateString,
                        'count' => (int) $row['count'],
                    ];
                    $found = true;

                    break;
                }
            }

            if (!$found) {
                $chartData[] = [
                    'date' => $dateString,
                    'count' => 0,
                ];
            }

            $currentDate->modify('+1 day');
        }

        return $chartData;
    }

    public function deleteByUrl(string $sourceUrl): void
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete($this->getClassName(), 'nfl')
           ->where('nfl.urlSlug = :sourceUrl')
           ->setParameter('sourceUrl', $sourceUrl);

        $qb->getQuery()->execute();
    }

    public function deleteByUrlAndDomain(string $sourceUrl, string $domain): void
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete($this->getClassName(), 'nfl')
           ->where('nfl.urlDomain = :domain')
           ->andWhere('nfl.urlSlug = :sourceUrl')
           ->setParameter('sourceUrl', $sourceUrl)
           ->setParameter('domain', $domain);

        $qb->getQuery()->execute();
    }

    public function countLogsOlderThan(\DateTimeInterface $date): int
    {
        return (int) $this->createQueryBuilder('nfl')
            ->select('COUNT(nfl.id)')
            ->where('nfl.createdAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function deleteLogsOlderThan(\DateTimeInterface $date, int $batchSize): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete($this->getClassName(), 'nfl')
           ->where('nfl.createdAt < :date')
           ->setParameter('date', $date)
           ->setMaxResults($batchSize);

        return (int) $qb->getQuery()->execute();
    }
}
