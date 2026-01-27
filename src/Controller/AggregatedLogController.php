<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ThreeBRS\Sylius404LogPlugin\Repository\NotFoundLogRepository;

class AggregatedLogController extends AbstractController
{
    private NotFoundLogRepository $repository;

    private EntityManagerInterface $entityManager;

    public function __construct(NotFoundLogRepository $repository, EntityManagerInterface $entityManager)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    public function indexAction(Request $request): Response
    {
        // Pagination parameters
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 20; // Number of records per page

        // Get filters from request
        $domainFilter = $request->query->get('domain', '');
        $urlPathFilter = $request->query->get('urlPath', '');
        $minCountFilter = $request->query->get('minCount', '');
        $maxCountFilter = $request->query->get('maxCount', '');

        // Use direct SQL query instead of QueryBuilder for PostgreSQL compatibility
        $connection = $this->entityManager->getConnection();

        // Build basic SQL query
        $sql = 'SELECT
            nfl.url_domain as "urlDomain",
            nfl.url_slug as "urlSlug",
            COUNT(nfl.id) as "logCount",
            MAX(nfl.created_at) as "lastOccurrence",
            MIN(nfl.created_at) as "firstOccurrence"
        FROM three_brs_404_not_found_log nfl';

        $whereConditions = [];
        $parameters = [];

        // Add WHERE conditions based on filters
        if (!empty($domainFilter)) {
            $whereConditions[] = 'nfl.url_domain LIKE :domain';
            $parameters['domain'] = '%' . $domainFilter . '%';
        }

        if (!empty($urlPathFilter)) {
            $whereConditions[] = 'nfl.url_slug LIKE :urlPath';
            $parameters['urlPath'] = '%' . $urlPathFilter . '%';
        }

        // Add WHERE clause if conditions exist
        if (!empty($whereConditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereConditions);
        }

        // Add GROUP BY
        $sql .= ' GROUP BY nfl.url_domain, nfl.url_slug';

        // Add HAVING conditions for count filters
        $havingConditions = [];

        if (!empty($minCountFilter) && is_numeric($minCountFilter)) {
            $havingConditions[] = 'COUNT(nfl.id) >= :minCount';
            $parameters['minCount'] = (int) $minCountFilter;
        }

        if (!empty($maxCountFilter) && is_numeric($maxCountFilter)) {
            $havingConditions[] = 'COUNT(nfl.id) <= :maxCount';
            $parameters['maxCount'] = (int) $maxCountFilter;
        }

        if (!empty($havingConditions)) {
            $sql .= ' HAVING ' . implode(' AND ', $havingConditions);
        }

        // Add ORDER BY
        $sql .= ' ORDER BY COUNT(nfl.id) DESC';

        // First, count total records for pagination
        $countStmt = $connection->prepare($sql);
        foreach ($parameters as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countResults = $countStmt->executeQuery()->fetchAllAssociative();
        $totalItems = count($countResults);
        $totalPages = ceil($totalItems / $limit);

        // Add LIMIT for pagination (standard SQL syntax compatible with both MySQL and PostgreSQL)
        $offset = ($page - 1) * $limit;
        $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;

        // Get data for current page
        $dataStmt = $connection->prepare($sql);
        foreach ($parameters as $key => $value) {
            $dataStmt->bindValue($key, $value);
        }
        $aggregatedData = $dataStmt->executeQuery()->fetchAllAssociative();

        return $this->render('@ThreeBRSSylius404LogPlugin/Admin/AggregatedLog/index.html.twig', [
            'aggregatedData' => $aggregatedData,
            'filters' => [
                'domain' => $domainFilter,
                'urlPath' => $urlPathFilter,
                'minCount' => $minCountFilter,
                'maxCount' => $maxCountFilter,
            ],
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
                'limit' => $limit,
                'hasNextPage' => $page < $totalPages,
                'hasPreviousPage' => $page > 1,
            ],
        ]);
    }

    public function deleteAllAction(Request $request): Response
    {
        $domain = $request->query->get('domain');
        $slug = $request->query->get('slug');

        if (!$domain || !$slug) {
            $this->addFlash('error', 'Domain and slug parameters are required');

            return $this->redirectToRoute('three_brs_sylius_404_log_plugin_admin_aggregated_log_index');
        }

        // Find all logs for the given domain and slug
        $logs = $this->repository->findByDomainAndSlug($domain, $slug);
        $count = count($logs);

        if ($count > 0) {
            // Delete all found logs
            foreach ($logs as $log) {
                $this->entityManager->remove($log);
            }

            $this->entityManager->flush();

            $this->addFlash('success', sprintf('Successfully deleted %d log entries for %s%s', $count, $domain, $slug));
        } else {
            $this->addFlash('info', 'No log entries found to delete');
        }

        return $this->redirectToRoute('three_brs_sylius_404_log_plugin_admin_aggregated_log_index');
    }
}
