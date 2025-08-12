<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ThreeBRS\Sylius404LogPlugin\Repository\NotFoundLogRepository;

class AggregatedLogDetailsController extends AbstractController
{
    private NotFoundLogRepository $notFoundLogRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(NotFoundLogRepository $notFoundLogRepository, EntityManagerInterface $entityManager)
    {
        $this->notFoundLogRepository = $notFoundLogRepository;
        $this->entityManager = $entityManager;
    }

    public function indexAction(Request $request): Response
    {
        $domain = $request->query->get('domain');
        $slug = $request->query->get('slug');

        if (!$domain || !$slug) {
            throw $this->createNotFoundException('Domain and slug parameters are required');
        }

        $logs = $this->notFoundLogRepository->findByDomainAndSlug($domain, $slug);
        $stats = $this->notFoundLogRepository->getAggregatedStats($domain, $slug);
        $chartData = $this->notFoundLogRepository->getChartData($domain, $slug);

        return $this->render('@ThreeBRSSylius404LogPlugin/Admin/AggregatedLog/details.html.twig', [
            'domain' => $domain,
            'slug' => $slug,
            'logs' => $logs,
            'stats' => $stats,
            'chartData' => $chartData,
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
        $logs = $this->notFoundLogRepository->findByDomainAndSlug($domain, $slug);
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
