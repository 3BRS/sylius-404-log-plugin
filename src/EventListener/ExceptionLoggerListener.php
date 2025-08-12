<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Resource\Factory\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionLoggerListener
{
    private EntityManagerInterface $entityManager;

    /** @var string[] */
    private array $skipPatterns;

    private LoggerInterface $logger;

    private Factory $notFoundLogFactory;

    /**
     * @param string[] $skipPatterns Patterns to skip logging, e.g. ['/admin', '/api']
     */
    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        Factory $notFoundLogFactory,
        array $skipPatterns = [],
    ) {
        $this->entityManager = $entityManager;
        $this->skipPatterns = $skipPatterns;
        $this->logger = $logger;
        $this->notFoundLogFactory = $notFoundLogFactory;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Pouze logujeme 404 chyby
        if (!$exception instanceof NotFoundHttpException) {
            return;
        }

        $request = $event->getRequest();

        // Nelogujeme admin sekci ani API
        if ($this->shouldSkipLogging($request)) {
            return;
        }

        $this->logNotFoundException($request);
    }

    private function shouldSkipLogging(Request $request): bool
    {
        $pathInfo = $request->getPathInfo();

        // Použijeme konfigurovatelné vzory místo hardcoded
        foreach ($this->skipPatterns as $pattern) {
            if (strpos($pathInfo, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    private function logNotFoundException(Request $request): void
    {
        try {
            $notFoundLog = $this->notFoundLogFactory->createNew();
            $notFoundLog->setUrlDomain($request->getHost());
            $notFoundLog->setUrlSlug($request->getPathInfo());
            $notFoundLog->setQueryString($request->getQueryString());
            $notFoundLog->setUserAgent($request->headers->get('User-Agent'));

            $this->entityManager->persist($notFoundLog);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $this->logger->error('Failed to log 404 error', [
                'exception' => $e,
                'url' => $request->getUri(),
                'user_agent' => $request->headers->get('User-Agent'),
            ]);
        }
    }
}
