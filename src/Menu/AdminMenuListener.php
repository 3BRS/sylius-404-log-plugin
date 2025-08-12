<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\Menu;

use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

class AdminMenuListener
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $this->addChild($menu);
    }

    private function addChild(ItemInterface $menu): void
    {
        $catalogMenu = $menu
            ->addChild('404_logs', ['position' => 60])
            ->setLabel('three_brs_sylius_404_log_plugin.ui.not_found_logs')
            ->setLabelAttribute('icon', 'file alternate outline');

        $catalogMenu
            ->addChild('not_found_logs', [
                'route' => 'three_brs_sylius_404_log_plugin_admin_not_found_log_index',
            ])
            ->setLabel('three_brs_sylius_404_log_plugin.ui.not_found_logs')
            ->setLabelAttribute('icon', 'list');

        $catalogMenu
            ->addChild('aggregated_logs', [
                'route' => 'three_brs_sylius_404_log_plugin_admin_aggregated_log_index',
            ])
            ->setLabel('three_brs_sylius_404_log_plugin.ui.aggregated_logs')
            ->setLabelAttribute('icon', 'chart bar outline');
    }
}
