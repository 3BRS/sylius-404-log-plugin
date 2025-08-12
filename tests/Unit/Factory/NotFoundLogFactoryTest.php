<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\Sylius404LogPlugin\Unit\Factory;

use PHPUnit\Framework\TestCase;
use ThreeBRS\Sylius404LogPlugin\Entity\NotFoundLog;
use ThreeBRS\Sylius404LogPlugin\Entity\NotFoundLogInterface;
use ThreeBRS\Sylius404LogPlugin\Factory\NotFoundLogFactory;

class NotFoundLogFactoryTest extends TestCase
{
    private NotFoundLogFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new NotFoundLogFactory();
    }

    public function testCreateNewReturnsNotFoundLogInterface(): void
    {
        $notFoundLog = $this->factory->createNew();

        $this->assertInstanceOf(NotFoundLogInterface::class, $notFoundLog);
        $this->assertInstanceOf(NotFoundLog::class, $notFoundLog);
    }

    public function testCreateNewReturnsNewInstanceEachTime(): void
    {
        $notFoundLog1 = $this->factory->createNew();
        $notFoundLog2 = $this->factory->createNew();

        $this->assertNotSame($notFoundLog1, $notFoundLog2);
    }

    public function testCreateNewSetsCreatedAtAutomatically(): void
    {
        $notFoundLog = $this->factory->createNew();

        $this->assertInstanceOf(\DateTimeImmutable::class, $notFoundLog->getCreatedAt());
        $this->assertEqualsWithDelta(
            new \DateTimeImmutable(),
            $notFoundLog->getCreatedAt(),
            1 // 1 second tolerance
        );
    }
}
