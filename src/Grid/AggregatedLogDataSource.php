<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\Grid;

use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Grid\Data\DataSourceInterface;
use Sylius\Component\Grid\Data\ExpressionBuilderInterface;
use Sylius\Component\Grid\Parameters;

class AggregatedLogDataSource implements DataSourceInterface
{
    private QueryBuilder $queryBuilder;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @return mixed
     */
    public function getData(Parameters $parameters)
    {
        $queryBuilder = clone $this->queryBuilder;

        // Nejprve získáme celkový počet bez paginace
        $countQueryBuilder = clone $queryBuilder;
        $totalResults = $countQueryBuilder->getQuery()->getResult();
        $totalCount = count($totalResults);

        // Aplikujeme paginaci
        $page = $parameters->get('page', 1);
        $limit = $parameters->get('limit', 10);
        $offset = ($page - 1) * $limit;

        $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        // Získáme data s paginací
        $results = $queryBuilder->getQuery()->getResult();

        // Převedeme array data na objekty pro Sylius grid
        $objectResults = [];
        foreach ($results as $row) {
            $objectResults[] = new AggregatedLogItem($row);
        }

        // Vytvoříme jednoduchý objekt s potřebnými metodami pro paginaci
        $result = new class($objectResults, $totalCount, $page, $limit) {
            /** @var array<int, AggregatedLogItem> */
            private array $data;

            private int $totalCount;

            private int $currentPage;

            private int $limit;

            /**
             * @param array<int, AggregatedLogItem> $data
             */
            public function __construct(array $data, int $totalCount, int $currentPage, int $limit)
            {
                $this->data = $data;
                $this->totalCount = $totalCount;
                $this->currentPage = $currentPage;
                $this->limit = $limit;
            }

            public function haveToPaginate(): bool
            {
                return $this->totalCount > $this->limit;
            }

            public function count(): int
            {
                return $this->totalCount;
            }

            public function getCurrentPage(): int
            {
                return $this->currentPage;
            }

            public function getLastPage(): int
            {
                return (int) ceil($this->totalCount / $this->limit);
            }

            public function getMaxPerPage(): int
            {
                return $this->limit;
            }

            public function getIterator(): \Iterator
            {
                return new \ArrayIterator($this->data);
            }

            // Pro kompatibilitu s foreach
            /**
             * @return array<int, AggregatedLogItem>
             */
            public function toArray(): array
            {
                return $this->data;
            }
        };

        return $result;
    }

    /**
     * @return array<int, AggregatedLogItem>
     */
    public function getDataAsArray(Parameters $parameters): array
    {
        $data = $this->getData($parameters);

        if (is_object($data) && method_exists($data, 'toArray')) {
            /** @var array<int, AggregatedLogItem> $arr */
            $arr = $data->toArray();

            return $arr;
        }

        if ($data instanceof \Traversable) {
            /** @var array<int, AggregatedLogItem> $arr */
            $arr = iterator_to_array($data);

            return $arr;
        }

        if (is_array($data)) {
            /** @var array<int, AggregatedLogItem> $arr */
            $arr = $data;

            return $arr;
        }

        return [];
    }

    public function restrict($expression, string $condition = DataSourceInterface::CONDITION_AND): void
    {
        // Pro agregované data nepotřebujeme restrict funkcionalitu
    }

    public function getExpressionBuilder(): ExpressionBuilderInterface
    {
        // Vrátíme prázdnou implementaci pro agregované data
        return new class() implements ExpressionBuilderInterface {
            /**
             * @param mixed ...$expressions
             *
             * @return mixed
             */
            public function andX(...$expressions)
            {
                return null;
            }

            /**
             * @param mixed ...$expressions
             *
             * @return mixed
             */
            public function orX(...$expressions)
            {
                return null;
            }

            /**
             * @param mixed $value
             *
             * @return mixed
             */
            public function comparison(string $field, string $operator, $value)
            {
                return null;
            }

            /**
             * @return mixed
             */
            public function like(string $field, string $value)
            {
                return null;
            }

            /**
             * @return mixed
             */
            public function notLike(string $field, string $value)
            {
                return null;
            }

            /**
             * @param mixed $value
             *
             * @return mixed
             */
            public function equal(string $field, $value)
            {
                return null;
            }

            /**
             * @param mixed $value
             *
             * @return mixed
             */
            public function notEqual(string $field, $value)
            {
                return null;
            }

            /**
             * @param mixed $value
             *
             * @return mixed
             */
            public function lessThan(string $field, $value)
            {
                return null;
            }

            /**
             * @param mixed $value
             *
             * @return mixed
             */
            public function lessThanOrEqual(string $field, $value)
            {
                return null;
            }

            /**
             * @param mixed $value
             *
             * @return mixed
             */
            public function greaterThan(string $field, $value)
            {
                return null;
            }

            /**
             * @param mixed $value
             *
             * @return mixed
             */
            public function greaterThanOrEqual(string $field, $value)
            {
                return null;
            }

            /**
             * @param array<int, mixed> $values
             *
             * @return mixed
             */
            public function in(string $field, array $values)
            {
                return null;
            }

            /**
             * @param array<int, mixed> $values
             *
             * @return mixed
             */
            public function notIn(string $field, array $values)
            {
                return null;
            }

            /**
             * @return mixed
             */
            public function isNull(string $field)
            {
                return null;
            }

            /**
             * @return mixed
             */
            public function isNotNull(string $field)
            {
                return null;
            }

            /**
             * @param mixed $value
             *
             * @return mixed
             */
            public function equals(string $field, $value)
            {
                return null;
            }

            /**
             * @param mixed $value
             *
             * @return mixed
             */
            public function notEquals(string $field, $value)
            {
                return null;
            }

            /**
             * @return mixed
             */
            public function orderBy(string $field, string $direction)
            {
                return null;
            }

            /**
             * @return mixed
             */
            public function addOrderBy(string $field, string $direction)
            {
                return null;
            }
        };
    }
}
