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
use ThreeBRS\Sylius404LogPlugin\EventListener\ExceptionLoggerListener;

class ExceptionLoggerListenerTest extends TestCase
{
    private MockObject|LoggerInterface $logger;
    private MockObject|EntityManagerInterface $entityManager;
    private ExceptionLoggerListener $listener;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->listener = new ExceptionLoggerListener(
            $this->logger,
            $this->entityManager,
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

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(\ThreeBRS\Sylius404LogPlugin\Entity\NotFoundLog::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->listener->onKernelException($event);
    }

    public function testOnKernelExceptionSkipsAdminPaths(): void
    {
        $exception = new NotFoundHttpException('Page not found');
        $request = new Request();
        $request->server->set('REQUEST_URI', '/admin/some-page');

        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->entityManager->expects($this->never())->method('persist');

        $this->listener->onKernelException($event);
    }

    public function testOnKernelExceptionSkipsApiPaths(): void
    {
        $exception = new NotFoundHttpException('Page not found');
        $request = new Request();
        $request->server->set('REQUEST_URI', '/api/some-endpoint');

        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->entityManager->expects($this->never())->method('persist');

        $this->listener->onKernelException($event);
    }

    public function testOnKernelExceptionHandlesEntityManagerException(): void
    {
        $exception = new NotFoundHttpException('Page not found');
        $request = new Request([], [], [], [], [], [
            'HTTP_HOST' => 'example.com',
        ]);

        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Failed to log 404 error', $this->isType('array'));

        $this->listener->onKernelException($event);
    }

    public function testOnKernelExceptionWithQueryString(): void
    {
        $exception = new NotFoundHttpException('Page not found');
        $request = new Request(['param' => 'value'], [], [], [], [], [
            'REQUEST_URI' => '/some-page?param=value',
            'HTTP_HOST' => 'example.com',
            'QUERY_STRING' => 'param=value'
        ]);

        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(\ThreeBRS\Sylius404LogPlugin\Entity\NotFoundLog::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->listener->onKernelException($event);
    }
}
