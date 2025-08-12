<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\EventListener;

use Doctrine\ORM\EntityManagerInterface;
//use Setono\SyliusRedirectPlugin\Repository\RedirectRepositoryInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use ThreeBRS\Sylius404LogPlugin\Entity\NotFoundLog;

final class ExceptionLoggerListener
{
    private EntityManagerInterface $entityManager;
//    private RedirectRepositoryInterface $redirectRepository;

    public function __construct(
//        RedirectRepositoryInterface $redirectRepository,
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
//        $this->redirectRepository = $redirectRepository;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($exception instanceof NotFoundHttpException) {
//            $redirect = $this->redirectRepository->findEnabledBySource($event->getRequest()->getPathInfo());
//            if (null === $redirect) {
            $request = $event->getRequest();

            $log = new NotFoundLog();
            $log->setUrlDomain($request->getHost());
            $log->setUrlSlug($request->getPathInfo());
            $log->setQueryString($request->getQueryString());
            $log->setUserAgent($request->headers->get('User-Agent'));

            $this->entityManager->persist($log);
            $this->entityManager->flush();
//            }
        }
    }
}
