<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\Sylius404LogPlugin\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use ThreeBRS\Sylius404LogPlugin\Entity\NotFoundLogInterface;
use ThreeBRS\Sylius404LogPlugin\EventListener\ExceptionLoggerListener;
use ThreeBRS\Sylius404LogPlugin\Factory\NotFoundLogFactoryInterface;

class ExceptionLoggerListenerTest extends TestCase
{
    private MockObject|LoggerInterface $logger;
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|NotFoundLogFactoryInterface $notFoundLogFactory;
    private MockObject|NotFoundLogInterface $notFoundLog;
    private ExceptionLoggerListener $listener;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->notFoundLogFactory = $this->createMock(NotFoundLogFactoryInterface::class);
        $this->notFoundLog = $this->createMock(NotFoundLogInterface::class);

        $this->listener = new ExceptionLoggerListener(
            $this->logger,
            $this->entityManager,
            $this->notFoundLogFactory,
            ['/admin', '/api']
        );
    }

    public function testOnKernelExceptionIgnoresNonNotFoundExceptions(): void
    {
        $exception = new \RuntimeException('Some error');
        $request = new Request();
        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->notFoundLogFactory->expects($this->never())->method('createNew');
        $this->entityManager->expects($this->never())->method('persist');

        $this->listener->onKernelException($event);
    }

    public function testOnKernelExceptionLogsNotFoundHttpException(): void
    {
        $exception = new NotFoundHttpException('Page not found');
        $request = new Request([], [], [], [], [], [
            'REQUEST_URI' => '/some-page',
            'HTTP_HOST' => 'example.com',
            'HTTP_USER_AGENT' => 'Mozilla/5.0'
        ]);
        $request->server->set('REQUEST_URI', '/some-page');

        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->notFoundLogFactory->expects($this->once())
            ->method('createNew')
            ->willReturn($this->notFoundLog);

        $this->notFoundLog->expects($this->once())
            ->method('setUrlDomain')
            ->with('example.com');

        $this->notFoundLog->expects($this->once())
            ->method('setUrlSlug')
            ->with('/some-page');

        $this->notFoundLog->expects($this->once())
            ->method('setQueryString')
            ->with(null);

        $this->notFoundLog->expects($this->once())
            ->method('setUserAgent')
            ->with('Mozilla/5.0');

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->notFoundLog);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->listener->onKernelException($event);
    }

    public function testOnKernelExceptionSkipsAdminPaths(): void
    {
        $exception = new NotFoundHttpException('Page not found');
        $request = new Request([], [], [], [], [], [
            'REQUEST_URI' => '/admin/some-page',
            'HTTP_HOST' => 'example.com'
        ]);
        $request->server->set('REQUEST_URI', '/admin/some-page');

        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->notFoundLogFactory->expects($this->never())->method('createNew');
        $this->entityManager->expects($this->never())->method('persist');

        $this->listener->onKernelException($event);
    }

    public function testOnKernelExceptionSkipsApiPaths(): void
    {
        $exception = new NotFoundHttpException('Page not found');
        $request = new Request([], [], [], [], [], [
            'REQUEST_URI' => '/api/v1/products',
            'HTTP_HOST' => 'example.com'
        ]);
        $request->server->set('REQUEST_URI', '/api/v1/products');

        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->notFoundLogFactory->expects($this->never())->method('createNew');
        $this->entityManager->expects($this->never())->method('persist');

        $this->listener->onKernelException($event);
    }

    public function testOnKernelExceptionHandlesEntityManagerException(): void
    {
        $exception = new NotFoundHttpException('Page not found');
        $request = new Request([], [], [], [], [], [
            'REQUEST_URI' => '/some-page',
            'HTTP_HOST' => 'example.com',
            'HTTP_USER_AGENT' => 'Mozilla/5.0'
        ]);
        $request->server->set('REQUEST_URI', '/some-page');

        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->notFoundLogFactory->expects($this->once())
            ->method('createNew')
            ->willReturn($this->notFoundLog);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Failed to log 404 error',
                $this->callback(function ($context) {
                    return isset($context['exception']) &&
                           isset($context['url']) &&
                           isset($context['user_agent']);
                })
            );

        $this->listener->onKernelException($event);
    }

    public function testOnKernelExceptionWithQueryString(): void
    {
        $exception = new NotFoundHttpException('Page not found');
        $request = new Request(['param' => 'value'], [], [], [], [], [
            'REQUEST_URI' => '/some-page?param=value',
            'QUERY_STRING' => 'param=value',
            'HTTP_HOST' => 'example.com',
            'HTTP_USER_AGENT' => 'Mozilla/5.0'
        ]);
        $request->server->set('REQUEST_URI', '/some-page');
        $request->server->set('QUERY_STRING', 'param=value');

        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->notFoundLogFactory->expects($this->once())
            ->method('createNew')
            ->willReturn($this->notFoundLog);

        $this->notFoundLog->expects($this->once())
            ->method('setQueryString')
            ->with('param=value');

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->listener->onKernelException($event);
    }
}
