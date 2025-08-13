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
        // Pagination parametry
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 20; // Počet záznamů na stránku

        // Získáme filtry z requestu
        $domainFilter = $request->query->get('domain', '');
        $urlPathFilter = $request->query->get('urlPath', '');
        $minCountFilter = $request->query->get('minCount', '');
        $maxCountFilter = $request->query->get('maxCount', '');

        // Nejprve spočítáme celkový počet záznamů pro pagination
        $countQueryBuilder = $this->repository->createAggregatedQueryBuilder();

        // Aplikujeme stejné filtry na count query
        if (!empty($domainFilter)) {
            $countQueryBuilder->andHaving('urlDomain LIKE :domain')
                ->setParameter('domain', '%' . $domainFilter . '%');
        }

        if (!empty($urlPathFilter)) {
            $countQueryBuilder->andHaving('urlSlug LIKE :urlPath')
                ->setParameter('urlPath', '%' . $urlPathFilter . '%');
        }

        if (!empty($minCountFilter) && is_numeric($minCountFilter)) {
            $countQueryBuilder->andHaving('logCount >= :minCount')
                ->setParameter('minCount', (int) $minCountFilter);
        }

        if (!empty($maxCountFilter) && is_numeric($maxCountFilter)) {
            $countQueryBuilder->andHaving('logCount <= :maxCount')
                ->setParameter('maxCount', (int) $maxCountFilter);
        }

        // Spočítáme celkový počet záznamů
        $totalItems = count($countQueryBuilder->getQuery()->getResult());
        $totalPages = ceil($totalItems / $limit);

        // Nyní získáme data pro aktuální stránku
        $dataQueryBuilder = $this->repository->createAggregatedQueryBuilder();

        // Aplikujeme stejné filtry na data query
        if (!empty($domainFilter)) {
            $dataQueryBuilder->andHaving('urlDomain LIKE :domain')
                ->setParameter('domain', '%' . $domainFilter . '%');
        }

        if (!empty($urlPathFilter)) {
            $dataQueryBuilder->andHaving('urlSlug LIKE :urlPath')
                ->setParameter('urlPath', '%' . $urlPathFilter . '%');
        }

        if (!empty($minCountFilter) && is_numeric($minCountFilter)) {
            $dataQueryBuilder->andHaving('logCount >= :minCount')
                ->setParameter('minCount', (int) $minCountFilter);
        }

        if (!empty($maxCountFilter) && is_numeric($maxCountFilter)) {
            $dataQueryBuilder->andHaving('logCount <= :maxCount')
                ->setParameter('maxCount', (int) $maxCountFilter);
        }

        // Přidáme pagination pouze na data query
        $dataQueryBuilder->setFirstResult(($page - 1) * $limit)
                        ->setMaxResults($limit);

        $aggregatedData = $dataQueryBuilder->getQuery()->getResult();

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

        // Najdeme všechny logy pro danou doménu a slug
        $logs = $this->repository->findByDomainAndSlug($domain, $slug);
        $count = count($logs);

        if ($count > 0) {
            // Smažeme všechny nalezené logy
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
