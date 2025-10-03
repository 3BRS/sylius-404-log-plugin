<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\Sylius404LogPlugin\Behat\Page\Admin\NotFoundLog;

use Behat\Mink\Element\NodeElement;
use Sylius\Behat\Page\Admin\Crud\IndexPage as BaseIndexPage;

final class IndexPage extends BaseIndexPage implements IndexPageInterface
{
    public function countItems(): int
    {
        return count($this->getTableBodyRows());
    }

    /**
     * @return array<NodeElement>
     */
    private function getTableBodyRows(): array
    {
        return $this->getDocument()->findAll('css', 'table tbody tr');
    }

    public function hasLogForUrl(string $url): bool
    {
        foreach ($this->getTableBodyRows() as $row) {
            $content = $row->getText();
            if (str_contains($content, $url)) {
                return true;
            }
        }

        return false;
    }

    public function hasLogForDomain(string $domain): bool
    {
        foreach ($this->getTableBodyRows() as $row) {
            $content = $row->getText();
            if (str_contains($content, $domain)) {
                return true;
            }
        }

        return false;
    }

    public function getRouteName(): string
    {
        return 'three_brs_sylius_404_log_plugin_admin_not_found_log_index';
    }
}