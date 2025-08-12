<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\Grid;

use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Data\DriverInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;
use ThreeBRS\Sylius404LogPlugin\Repository\NotFoundLogRepository;

class AggregatedLogDataProvider implements DriverInterface
{
    private NotFoundLogRepository $repository;

    public function __construct(NotFoundLogRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getDataSourceName(): string
    {
        return 'aggregated_log';
    }

    /**
     * @param mixed[] $configuration
     */
    public function getDataSource(array $configuration, Parameters $parameters): DataSourceInterface
    {
        $queryBuilder = $this->repository->createAggregatedQueryBuilder();

        // Aplikujeme filtry z Sylius grid
        $criteria = $parameters->get('criteria', []);

        if (isset($criteria['urlDomain']['value']) && !empty($criteria['urlDomain']['value'])) {
            $queryBuilder->andHaving('urlDomain LIKE :domain')
                ->setParameter('domain', '%' . $criteria['urlDomain']['value'] . '%');
        }

        if (isset($criteria['urlSlug']['value']) && !empty($criteria['urlSlug']['value'])) {
            $queryBuilder->andHaving('urlSlug LIKE :slug')
                ->setParameter('slug', '%' . $criteria['urlSlug']['value'] . '%');
        }

        if (isset($criteria['minCount']['value']) && !empty($criteria['minCount']['value'])) {
            $queryBuilder->andHaving('count >= :minCount')
                ->setParameter('minCount', (int) $criteria['minCount']['value']);
        }

        if (isset($criteria['maxCount']['value']) && !empty($criteria['maxCount']['value'])) {
            $queryBuilder->andHaving('count <= :maxCount')
                ->setParameter('maxCount', (int) $criteria['maxCount']['value']);
        }

        // Aplikujeme řazení z Sylius grid
        $sorting = $parameters->get('sorting', []);
        foreach ($sorting as $field => $direction) {
            if ($field === 'count') {
                $queryBuilder->orderBy('count', $direction);
            } elseif ($field === 'urlDomain') {
                $queryBuilder->orderBy('urlDomain', $direction);
            } elseif ($field === 'urlSlug') {
                $queryBuilder->orderBy('urlSlug', $direction);
            } elseif ($field === 'lastOccurrence') {
                $queryBuilder->orderBy('lastOccurrence', $direction);
            }
        }

        return new AggregatedLogDataSource($queryBuilder);
    }

    public function getData(Grid $grid, Parameters $parameters): mixed
    {
        $dataSource = $this->getDataSource([], $parameters);

        return $dataSource->getData($parameters);
    }
}
