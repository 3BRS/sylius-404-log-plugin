<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\Sylius404LogPlugin\Behat\Page\Admin\AggregatedLog;

use Behat\Mink\Element\NodeElement;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;
use Webmozart\Assert\Assert;

final class IndexPage extends SymfonyPage implements IndexPageInterface
{
    public function getRouteName(): string
    {
        return 'three_brs_sylius_404_log_plugin_admin_aggregated_log_index';
    }

    public function countItems(): int
    {
        $rows = $this->getTable()?->findAll('css', 'tbody tr') ?? [];

        return count($rows);
    }

    private function getTable(): ?NodeElement
    {
        $tables = $this->getDocument()->findAll('css', '[data-test-aggregated-table]');
        if (count($tables) === 0) {
            return null;
        }
        Assert::count($tables, 1, 'Multiple tables found with data-test-aggregated-table attribute.');
        $table = reset($tables);

        Assert::isInstanceOf($table, NodeElement::class);

        return $table;
    }

    public function hasAggregatedLogForUrl(
        string $domain,
        string $urlSlug,
    ): bool {
        $rows = $this->getTable()?->findAll('css', 'tbody tr') ?? [];

        foreach ($rows as $row) {
            $domainCell = $row->find('css', 'td:nth-child(1)');
            $slugCell = $row->find('css', 'td:nth-child(2)');

            if ($domainCell && $slugCell) {
                if (trim($domainCell->getText()) === $domain && trim($slugCell->getText()) === $urlSlug) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getOccurrenceCount(
        string $domain,
        string $urlSlug,
    ): ?int {
        $rows = $this->getTable()?->findAll('css', 'tbody tr') ?? [];

        foreach ($rows as $row) {
            $domainCell = $row->find('css', 'td:nth-child(1)');
            $slugCell = $row->find('css', 'td:nth-child(2)');
            $countCell = $row->find('css', 'td:nth-child(3)');

            if ($domainCell && $slugCell && $countCell) {
                if (trim($domainCell->getText()) === $domain && trim($slugCell->getText()) === $urlSlug) {
                    return (int) trim($countCell->getText());
                }
            }
        }

        return null;
    }

    public function filterByDomain(string $domain): void
    {
        $input = $this->getElement('domain_filter');
        $input->setValue($domain);
        $form = $this->getDocument()->find('xpath', '//form[.//input[@name="domain"]]');
        Assert::notNull($form, 'Filter form not found');
        $form->submit();
    }

    public function filterByUrlPath(string $urlPath): void
    {
        $input = $this->getElement('url_path_filter');
        $input->setValue($urlPath);
        $form = $this->getDocument()->find('xpath', '//form[.//input[@name="urlPath"]]');
        Assert::notNull($form, 'Filter form not found');
        $form->submit();
    }

    public function filterByMinCount(int $minCount): void
    {
        $input = $this->getElement('min_count_filter');
        $input->setValue((string) $minCount);
        $form = $this->getDocument()->find('xpath', '//form[.//input[@name="minCount"]]');
        Assert::notNull($form, 'Filter form not found');
        $form->submit();
    }

    public function filterByMaxCount(int $maxCount): void
    {
        $input = $this->getElement('max_count_filter');
        $input->setValue((string) $maxCount);
        $form = $this->getDocument()->find('xpath', '//form[.//input[@name="maxCount"]]');
        Assert::notNull($form, 'Filter form not found');
        $form->submit();
    }

    public function deleteLogsFor(
        string $domain,
        string $urlSlug,
    ): void {
        $row = $this->findRowForLog($domain, $urlSlug);
        Assert::notNull($row);
        // Find delete button - it's a Bootstrap button with btn-outline-danger class
        $deleteButton = $row->find('css', 'a.btn-outline-danger, a.btn.btn-outline-danger');
        Assert::notNull($deleteButton);
        $deleteButton->click();
    }

    public function clickDetails(
        string $domain,
        string $urlSlug,
    ): void {
        $row = $this->findRowForLog($domain, $urlSlug);
        if ($row) {
            $detailsLink = $row->find('css', 'a.details-link, a[href*="details"]');
            if ($detailsLink) {
                $detailsLink->click();
            }
        }
    }

    private function findRowForLog(
        string $domain,
        string $urlSlug,
    ): ?NodeElement {
        $rows = $this->getTable()?->findAll('css', 'tbody tr') ?? [];

        foreach ($rows as $row) {
            $domainCell = $row->find('css', 'td:nth-child(1)');
            $slugCell = $row->find('css', 'td:nth-child(2)');

            if ($domainCell && $slugCell) {
                if (trim($domainCell->getText()) === $domain && trim($slugCell->getText()) === $urlSlug) {
                    return $row;
                }
            }
        }

        return null;
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'domain_filter' => 'input[name="domain"]',
            'url_path_filter' => 'input[name="urlPath"]',
            'min_count_filter' => 'input[name="minCount"]',
            'max_count_filter' => 'input[name="maxCount"]',
        ]);
    }
}
