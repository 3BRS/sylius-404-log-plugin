<?php

declare(strict_types=1);

namespace Tests\ThreeBRS\Sylius404LogPlugin\Behat\Page\Admin\AggregatedLog;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

final class DetailsPage extends SymfonyPage implements DetailsPageInterface
{
    public function getRouteName(): string
    {
        return 'three_brs_sylius_404_log_plugin_admin_aggregated_log_details';
    }

    public function getTotalCount(): int
    {
        $element = $this->getDocument()->find('css', '[data-test-total-count]');

        if ($element) {
            return (int) trim($element->getText());
        }

        return 0;
    }

    public function hasStatistic(string $label, string $value): bool
    {
        $content = $this->getDocument()->getContent();

        return strpos($content, $label) !== false && strpos($content, $value) !== false;
    }

    public function countIndividualLogs(): int
    {
        $table = $this->getDocument()->find('css', '[data-test-individual-logs-table]');

        if (!$table) {
            return 0;
        }

        $rows = $table->findAll('css', 'tbody tr');

        return count($rows);
    }

    public function hasChartData(): bool
    {
        $chartElement = $this->getDocument()->find('css', 'canvas, .chart, [data-chart]');

        return $chartElement !== null;
    }
}