<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

class AdminMenuListener
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $menu = $menu->getChild('configuration');
        assert($menu !== null);

        $menu
            ->addChild('logs', ['route' => 'three_brs_sylius_404_log_plugin_admin_not_found_log_index'])
            ->setLabelAttribute('icon', 'file alternate')
            ->setLabel('three_brs_sylius_404_log_plugin.ui.not_found_logs');
    }
}
