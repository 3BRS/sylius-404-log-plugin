<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\Sylius404LogPlugin\Unit\Entity;

use PHPUnit\Framework\TestCase;
use ThreeBRS\Sylius404LogPlugin\Entity\NotFoundLog;
use ThreeBRS\Sylius404LogPlugin\Entity\NotFoundLogInterface;

class NotFoundLogTest extends TestCase
{
    private NotFoundLog $notFoundLog;

    protected function setUp(): void
    {
        $this->notFoundLog = new NotFoundLog();
    }

    public function testImplementsNotFoundLogInterface(): void
    {
        $this->assertInstanceOf(NotFoundLogInterface::class, $this->notFoundLog);
    }

    public function testConstructorSetsCreatedAt(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->notFoundLog->getCreatedAt());
        $this->assertEqualsWithDelta(
            new \DateTimeImmutable(),
            $this->notFoundLog->getCreatedAt(),
            1 // 1 second tolerance
        );
    }

    public function testIdIsNullByDefault(): void
    {
        $this->assertNull($this->notFoundLog->getId());
    }

    public function testUrlDomainSetterAndGetter(): void
    {
        $domain = 'example.com';

        $this->notFoundLog->setUrlDomain($domain);

        $this->assertSame($domain, $this->notFoundLog->getUrlDomain());
    }

    public function testUrlSlugSetterAndGetter(): void
    {
        $slug = '/some/path/to/page';

        $this->notFoundLog->setUrlSlug($slug);

        $this->assertSame($slug, $this->notFoundLog->getUrlSlug());
    }

    public function testQueryStringSetterAndGetter(): void
    {
        $queryString = 'param1=value1&param2=value2';

        $this->notFoundLog->setQueryString($queryString);

        $this->assertSame($queryString, $this->notFoundLog->getQueryString());
    }

    public function testQueryStringCanBeNull(): void
    {
        $this->notFoundLog->setQueryString(null);

        $this->assertNull($this->notFoundLog->getQueryString());
    }

    public function testUserAgentSetterAndGetter(): void
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

        $this->notFoundLog->setUserAgent($userAgent);

        $this->assertSame($userAgent, $this->notFoundLog->getUserAgent());
    }

    public function testUserAgentCanBeNull(): void
    {
        $this->notFoundLog->setUserAgent(null);

        $this->assertNull($this->notFoundLog->getUserAgent());
    }

    public function testCreatedAtGetter(): void
    {
        $createdAt = $this->notFoundLog->getCreatedAt();

        $this->assertInstanceOf(\DateTimeImmutable::class, $createdAt);
        $this->assertLessThanOrEqual(new \DateTimeImmutable(), $createdAt);
    }

    public function testCompleteEntityFunctionality(): void
    {
        $domain = 'shop.example.com';
        $slug = '/products/non-existent-product';
        $queryString = 'color=red&size=large';
        $userAgent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

        $this->notFoundLog->setUrlDomain($domain);
        $this->notFoundLog->setUrlSlug($slug);
        $this->notFoundLog->setQueryString($queryString);
        $this->notFoundLog->setUserAgent($userAgent);

        $this->assertSame($domain, $this->notFoundLog->getUrlDomain());
        $this->assertSame($slug, $this->notFoundLog->getUrlSlug());
        $this->assertSame($queryString, $this->notFoundLog->getQueryString());
        $this->assertSame($userAgent, $this->notFoundLog->getUserAgent());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->notFoundLog->getCreatedAt());
    }
}
