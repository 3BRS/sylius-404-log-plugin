<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\Sylius404LogPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use ThreeBRS\Sylius404LogPlugin\Entity\NotFoundLog;

final readonly class NotFoundLogContext implements Context
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SharedStorageInterface $sharedStorage,
    ) {
    }

    /**
     * @Given there are no 404 logs in the database
     */
    public function thereAreNo404LogsInTheDatabase(): void
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->delete(NotFoundLog::class, 'nfl');
        $queryBuilder->getQuery()->execute();
        $this->entityManager->clear();
    }

    /**
     * @Given there is a 404 log for :urlSlug on domain :domain
     */
    public function thereIsA404LogFor(string $urlSlug, string $domain): void
    {
        $this->create404Log($domain, $urlSlug);
    }

    /**
     * @Given there is a 404 log for :urlSlug on domain :domain with user agent :userAgent
     */
    public function thereIsA404LogForWithUserAgent(string $urlSlug, string $domain, string $userAgent): void
    {
        $this->create404Log($domain, $urlSlug, userAgent: $userAgent);
    }

    /**
     * @Given there is a 404 log for :urlSlug on domain :domain with query string :queryString
     */
    public function thereIsA404LogForWithQueryString(string $urlSlug, string $domain, string $queryString): void
    {
        $this->create404Log($domain, $urlSlug, queryString: $queryString);
    }

    /**
     * @Given there are :count 404 logs for :urlSlug on domain :domain
     */
    public function thereAre404LogsFor(int $count, string $urlSlug, string $domain): void
    {
        for ($i = 0; $i < $count; ++$i) {
            $this->create404Log($domain, $urlSlug);
        }
    }

    /**
     * @Given there are :count 404 logs for :urlSlug on domain :domain created :daysAgo days ago
     */
    public function thereAre404LogsForCreatedDaysAgo(int $count, string $urlSlug, string $domain, int $daysAgo): void
    {
        $createdAt = new \DateTimeImmutable('-' . $daysAgo . ' days');
        for ($i = 0; $i < $count; ++$i) {
            $this->create404Log($domain, $urlSlug, $createdAt);
        }
    }

    /**
     * @Given there are the following 404 logs:
     */
    public function thereAreTheFollowing404Logs(TableNode $table): void
    {
        foreach ($table->getHash() as $row) {
            $count = (int) ($row['count'] ?? 1);
            $domain = $row['domain'];
            $urlSlug = $row['url_slug'];
            $userAgent = $row['user_agent'] ?? null;
            $queryString = $row['query_string'] ?? null;

            for ($i = 0; $i < $count; ++$i) {
                $this->create404Log($domain, $urlSlug, null, $userAgent, $queryString);
            }
        }
    }

    private function create404Log(
        string $domain,
        string $urlSlug,
        ?\DateTimeImmutable $createdAt = null,
        ?string $userAgent = null,
        ?string $queryString = null,
    ): void {
        $notFoundLog = new NotFoundLog();
        $notFoundLog->setUrlDomain($domain);
        $notFoundLog->setUrlSlug($urlSlug);
        $notFoundLog->setUserAgent($userAgent ?? 'Mozilla/5.0 (Test Browser)');
        $notFoundLog->setQueryString($queryString);

        if ($createdAt !== null) {
            $notFoundLog->setCreatedAt($createdAt);
        }

        $this->entityManager->persist($notFoundLog);
        $this->entityManager->flush();

        $this->sharedStorage->set('404_log', $notFoundLog);
    }
}
