<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="three_brs_404_not_found_log")
 */
#[ORM\Entity]
#[ORM\Table(name: 'three_brs_404_not_found_log')]
class NotFoundLog implements NotFoundLogInterface
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(type="integer")
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    /** @ORM\Column(type="string", name="url_domain") */
    #[ORM\Column(type: 'string', name: 'url_domain')]
    protected string $urlDomain;

    /** @ORM\Column(type="text", name="url_slug") */
    #[ORM\Column(type: 'text', name: 'url_slug')]
    protected string $urlSlug;

    /** @ORM\Column(type="text", name="query_string", nullable=true) */
    #[ORM\Column(type: 'text', name: 'query_string', nullable: true)]
    protected ?string $queryString = null;

    /** @ORM\Column(type="text", name="user_agent", nullable=true) */
    #[ORM\Column(type: 'text', name: 'user_agent', nullable: true)]
    protected ?string $userAgent = null;

    /** @ORM\Column(type="datetime_immutable", name="created_at") */
    #[ORM\Column(type: 'datetime_immutable', name: 'created_at')]
    protected \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }
}
