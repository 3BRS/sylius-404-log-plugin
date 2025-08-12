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
        // Získáme filtry z requestu
        $domainFilter = $request->query->get('domain', '');
        $urlPathFilter = $request->query->get('urlPath', '');
        $minCountFilter = $request->query->get('minCount', '');
        $maxCountFilter = $request->query->get('maxCount', '');

        // Získáme agregovaná data s filtry
        $queryBuilder = $this->repository->createAggregatedQueryBuilder();

        // Aplikujeme filtry
        if (!empty($domainFilter)) {
            $queryBuilder->andHaving('urlDomain LIKE :domain')
                ->setParameter('domain', '%' . $domainFilter . '%');
        }

        if (!empty($urlPathFilter)) {
            $queryBuilder->andHaving('urlSlug LIKE :urlPath')
                ->setParameter('urlPath', '%' . $urlPathFilter . '%');
        }

        if (!empty($minCountFilter) && is_numeric($minCountFilter)) {
            $queryBuilder->andHaving('logCount >= :minCount')
                ->setParameter('minCount', (int) $minCountFilter);
        }

        if (!empty($maxCountFilter) && is_numeric($maxCountFilter)) {
            $queryBuilder->andHaving('logCount <= :maxCount')
                ->setParameter('maxCount', (int) $maxCountFilter);
        }

        $aggregatedData = $queryBuilder->getQuery()->getResult();

        return $this->render('@ThreeBRSSylius404LogPlugin/Admin/AggregatedLog/index.html.twig', [
            'aggregatedData' => $aggregatedData,
            'filters' => [
                'domain' => $domainFilter,
                'urlPath' => $urlPathFilter,
                'minCount' => $minCountFilter,
                'maxCount' => $maxCountFilter,
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
