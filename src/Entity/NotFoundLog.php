<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'three_brs_404_not_found_log')]
#[ORM\Index(columns: ['url_domain'])]
#[ORM\Index(columns: ['url_slug'])]
#[ORM\Index(columns: ['created_at'])]
class NotFoundLog implements NotFoundLogInterface
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(name: 'url_domain', type: Types::STRING)]
    protected string $urlDomain;

    #[ORM\Column(name: 'url_slug', type: Types::STRING, length: 765)]
    protected string $urlSlug;

    #[ORM\Column(name: 'query_string', type: Types::TEXT, nullable: true)]
    protected ?string $queryString = null;

    #[ORM\Column(name: 'user_agent', type: Types::TEXT, nullable: true)]
    protected ?string $userAgent = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    protected \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrlDomain(): string
    {
        return $this->urlDomain;
    }

    public function setUrlDomain(string $urlDomain): void
    {
        $this->urlDomain = $urlDomain;
    }

    public function getUrlSlug(): string
    {
        return $this->urlSlug;
    }

    public function setUrlSlug(string $urlSlug): void
    {
        $this->urlSlug = $urlSlug;
    }

    public function getQueryString(): ?string
    {
        return $this->queryString;
    }

    public function setQueryString(?string $queryString): void
    {
        $this->queryString = $queryString;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
