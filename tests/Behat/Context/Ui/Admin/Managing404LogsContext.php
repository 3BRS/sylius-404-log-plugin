<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\Sylius404LogPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Mink\Element\NodeElement;
use Sylius\Behat\Exception\NotificationExpectationMismatchException;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Service\Accessor\NotificationAccessorInterface;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Tests\ThreeBRS\Sylius404LogPlugin\Behat\Page\Admin\AggregatedLog\DetailsPageInterface;
use Tests\ThreeBRS\Sylius404LogPlugin\Behat\Page\Admin\AggregatedLog\IndexPageInterface as AggregatedLogIndexPageInterface;
use Tests\ThreeBRS\Sylius404LogPlugin\Behat\Page\Admin\NotFoundLog\IndexPageInterface;
use Webmozart\Assert\Assert;

final readonly class Managing404LogsContext implements Context
{
    public function __construct(
        private IndexPageInterface              $notFoundLogIndexPage,
        private AggregatedLogIndexPageInterface $aggregatedLogIndexPage,
        private DetailsPageInterface            $aggregatedLogDetailsPage,
        private NotificationCheckerInterface    $notificationChecker,
        private NotificationAccessorInterface   $notificationAccessor,
    ) {
    }

    /**
     * @When I browse 404 logs
     * @When I go to the 404 logs page
     */
    public function iBrowse404Logs(): void
    {
        $this->notFoundLogIndexPage->open();
    }

    /**
     * @When I browse aggregated 404 logs
     * @When I go to the aggregated 404 logs page
     */
    public function iBrowseAggregated404Logs(): void
    {
        $this->aggregatedLogIndexPage->open();
    }

    /**
     * @When I view details for :domain :urlSlug
     */
    public function iViewDetailsFor(
        string $domain,
        string $urlSlug,
    ): void {
        $this->aggregatedLogDetailsPage->open(['domain' => $domain, 'slug' => $urlSlug]);
    }

    /**
     * @Then I should see :count 404 logs in the list
     */
    public function iShouldSee404LogsInTheList(int $count): void
    {
        Assert::same($this->notFoundLogIndexPage->countItems(), $count);
    }

    /**
     * @Then I should see :count aggregated 404 logs in the list
     */
    public function iShouldSeeAggregated404LogsInTheList(int $count): void
    {
        Assert::same($this->aggregatedLogIndexPage->countItems(), $count);
    }

    /**
     * @Then I should see a log for URL :url
     */
    public function iShouldSeeALogForUrl(string $url): void
    {
        Assert::true(
            $this->notFoundLogIndexPage->hasLogForUrl($url),
            sprintf('404 log for URL "%s" should be visible', $url),
        );
    }

    /**
     * @Then I should see a log for domain :domain
     */
    public function iShouldSeeALogForDomain(string $domain): void
    {
        Assert::true(
            $this->notFoundLogIndexPage->hasLogForDomain($domain),
            sprintf('404 log for domain "%s" should be visible', $domain),
        );
    }

    /**
     * @Then I should see an aggregated log for :domain :urlSlug
     */
    public function iShouldSeeAnAggregatedLogFor(
        string $domain,
        string $urlSlug,
    ): void {
        Assert::true(
            $this->aggregatedLogIndexPage->hasAggregatedLogForUrl($domain, $urlSlug),
            sprintf('Aggregated log for %s%s should be visible', $domain, $urlSlug),
        );
    }

    /**
     * @Then I should not see an aggregated log for :domain :urlSlug
     */
    public function iShouldNotSeeAnAggregatedLogFor(
        string $domain,
        string $urlSlug,
    ): void {
        Assert::false(
            $this->aggregatedLogIndexPage->hasAggregatedLogForUrl($domain, $urlSlug),
            sprintf('Aggregated log for %s%s should not be visible', $domain, $urlSlug),
        );
    }

    /**
     * @Then the aggregated log for :domain :urlSlug should show :count occurrences
     */
    public function theAggregatedLogForShouldShowOccurrences(
        string $domain,
        string $urlSlug,
        int    $count,
    ): void {
        $actualCount = $this->aggregatedLogIndexPage->getOccurrenceCount($domain, $urlSlug);
        Assert::same(
            $actualCount,
            $count,
            sprintf('Expected %d occurrences but got %d for %s%s', $count, $actualCount, $domain, $urlSlug),
        );
    }

    /**
     * @When I filter aggregated logs by domain :domain
     */
    public function iFilterAggregatedLogsByDomain(string $domain): void
    {
        $this->aggregatedLogIndexPage->filterByDomain($domain);
    }

    /**
     * @When I filter aggregated logs by URL path :urlPath
     */
    public function iFilterAggregatedLogsByUrlPath(string $urlPath): void
    {
        $this->aggregatedLogIndexPage->filterByUrlPath($urlPath);
    }

    /**
     * @When I filter aggregated logs by minimum count :minCount
     */
    public function iFilterAggregatedLogsByMinimumCount(int $minCount): void
    {
        $this->aggregatedLogIndexPage->filterByMinCount($minCount);
    }

    /**
     * @When I filter aggregated logs by maximum count :maxCount
     */
    public function iFilterAggregatedLogsByMaximumCount(int $maxCount): void
    {
        $this->aggregatedLogIndexPage->filterByMaxCount($maxCount);
    }

    /**
     * @When I delete logs for :domain :urlSlug
     */
    public function iDeleteLogsFor(
        string $domain,
        string $urlSlug,
    ): void {
        $this->aggregatedLogIndexPage->deleteLogsFor($domain, $urlSlug);
    }

    /**
     * @When I click details for :domain :urlSlug
     */
    public function iClickDetailsFor(
        string $domain,
        string $urlSlug,
    ): void {
        $this->aggregatedLogIndexPage->clickDetails($domain, $urlSlug);
    }

    /**
     * @Then I should see :count individual logs on the details page
     */
    public function iShouldSeeIndividualLogsOnTheDetailsPage(int $count): void
    {
        Assert::same(
            $this->aggregatedLogDetailsPage->countIndividualLogs(),
            $count,
            sprintf(
                'Expected %d individual logs on details page, got %d',
                $count,
                $this->aggregatedLogDetailsPage->countIndividualLogs(),
            ),
        );
    }

    /**
     * @Then I should see a chart with trend data
     */
    public function iShouldSeeAChartWithTrendData(): void
    {
        Assert::true(
            $this->aggregatedLogDetailsPage->hasChartData(),
            'Chart data should be visible on details page',
        );
    }

    /**
     * @Then I should see statistics for the 404 errors
     */
    public function iShouldSeeStatisticsForThe404Errors(): void
    {
        Assert::greaterThan(
            $this->aggregatedLogDetailsPage->getTotalCount(),
            0,
            'Statistics should be visible on details page',
        );
    }

    /**
     * @Then I should be notified that the logs have been deleted
     */
    public function iShouldBeNotifiedThatTheLogsHaveBeenDeleted(): void
    {
        try {
            $this->notificationChecker->checkNotification(
                'Successfully deleted',
                NotificationType::success(),
            );
        } catch (NotificationExpectationMismatchException $notificationExpectationMismatchException) {
            throw new \RuntimeException(
                message: $notificationExpectationMismatchException->getMessage()
                         . ' Instead, got: '
                         . implode(
                             ';',
                             array_map(
                                 static fn(
                                     NodeElement $nodeElement,
                                 ) => $nodeElement->getText(),
                                 $this->notificationAccessor->getMessageElements(),
                             ),
                         ),
                previous: $notificationExpectationMismatchException,
            );
        }
    }

    /**
     * @Then I should see empty list of 404 logs
     */
    public function iShouldSeeEmptyListOf404Logs(): void
    {
        Assert::same($this->notFoundLogIndexPage->countItems(), 0);
    }

    /**
     * @Then I should see empty list of aggregated 404 logs
     */
    public function iShouldSeeEmptyListOfAggregated404Logs(): void
    {
        Assert::same($this->aggregatedLogIndexPage->countItems(), 0);
    }
}
