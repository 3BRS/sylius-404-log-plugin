<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\Sylius404LogPlugin\Behat\Page\Admin\AggregatedLog;

use Behat\Mink\Element\NodeElement;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

final class IndexPage extends SymfonyPage implements IndexPageInterface
{
    public function getRouteName(): string
    {
        return 'three_brs_sylius_404_log_plugin_admin_aggregated_log_index';
    }

    public function countItems(): int
    {
        $rows = $this->getDocument()->findAll('css', 'table tbody tr');

        return count($rows);
    }

    public function hasAggregatedLogForUrl(string $domain, string $urlSlug): bool
    {
        $rows = $this->getDocument()->findAll('css', 'table tbody tr');

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

    public function getOccurrenceCount(string $domain, string $urlSlug): ?int
    {
        $rows = $this->getDocument()->findAll('css', 'table tbody tr');

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
        $this->getElement('domain_filter')->setValue($domain);
        $this->getElement('filter_button')->click();
    }

    public function filterByUrlPath(string $urlPath): void
    {
        $this->getElement('url_path_filter')->setValue($urlPath);
        $this->getElement('filter_button')->click();
    }

    public function filterByMinCount(int $minCount): void
    {
        $this->getElement('min_count_filter')->setValue((string) $minCount);
        $this->getElement('filter_button')->click();
    }

    public function filterByMaxCount(int $maxCount): void
    {
        $this->getElement('max_count_filter')->setValue((string) $maxCount);
        $this->getElement('filter_button')->click();
    }

    public function deleteLogsFor(string $domain, string $urlSlug): void
    {
        $row = $this->findRowForLog($domain, $urlSlug);
        if ($row) {
            $deleteButton = $row->find('css', 'a.red.button i.trash.icon');
            if ($deleteButton) {
                // Click the parent link, not the icon
                $deleteButton->getParent()->click();
            }
        }
    }

    public function clickDetails(string $domain, string $urlSlug): void
    {
        $row = $this->findRowForLog($domain, $urlSlug);
        if ($row) {
            $detailsLink = $row->find('css', 'a.details-link, a[href*="details"]');
            if ($detailsLink) {
                $detailsLink->click();
            }
        }
    }

    private function findRowForLog(string $domain, string $urlSlug): ?NodeElement
    {
        $rows = $this->getDocument()->findAll('css', 'table tbody tr');

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
            'filter_button' => 'button[type="submit"]',
        ]);
    }
}