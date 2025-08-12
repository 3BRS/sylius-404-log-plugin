<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

interface NotFoundLogInterface extends ResourceInterface
{
    public function getUrlDomain(): string;

    public function setUrlDomain(string $urlDomain): void;

    public function getUrlSlug(): string;

    public function setUrlSlug(string $urlSlug): void;

    public function getQueryString(): ?string;

    public function setQueryString(?string $queryString): void;

    public function getUserAgent(): ?string;

    public function setUserAgent(?string $userAgent): void;

    public function getCreatedAt(): \DateTimeImmutable;

    public function setCreatedAt(\DateTimeImmutable $createdAt): void;

    public function getId(): ?int;

    public function setId(?int $id): void;
}
