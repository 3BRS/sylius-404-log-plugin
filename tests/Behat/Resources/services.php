<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Tests\ThreeBRS\Sylius404LogPlugin\Behat\Context\Setup\NotFoundLogContext;
use Tests\ThreeBRS\Sylius404LogPlugin\Behat\Context\Ui\Admin\Managing404LogsContext;
use Tests\ThreeBRS\Sylius404LogPlugin\Behat\Page\Admin\AggregatedLog\DetailsPage;
use Tests\ThreeBRS\Sylius404LogPlugin\Behat\Page\Admin\AggregatedLog\DetailsPageInterface;
use Tests\ThreeBRS\Sylius404LogPlugin\Behat\Page\Admin\AggregatedLog\IndexPage as AggregatedLogIndexPage;
use Tests\ThreeBRS\Sylius404LogPlugin\Behat\Page\Admin\AggregatedLog\IndexPageInterface as AggregatedLogIndexPageInterface;
use Tests\ThreeBRS\Sylius404LogPlugin\Behat\Page\Admin\NotFoundLog\IndexPage as NotFoundLogIndexPage;
use Tests\ThreeBRS\Sylius404LogPlugin\Behat\Page\Admin\NotFoundLog\IndexPageInterface as NotFoundLogIndexPageInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->public();

    $services->set('tests.three_brs.sylius_404_log_plugin.behat.context.setup.not_found_log', NotFoundLogContext::class)
        ->args([
            service('doctrine.orm.entity_manager'),
            service('sylius.behat.shared_storage'),
        ])
        ->tag('fob.context_service');

    $services->set('tests.three_brs.sylius_404_log_plugin.behat.context.ui.admin.managing_404_logs', Managing404LogsContext::class)
        ->args([
            service(NotFoundLogIndexPageInterface::class),
            service(AggregatedLogIndexPageInterface::class),
            service(DetailsPageInterface::class),
            service('sylius.behat.notification_checker.admin'),
            service('sylius.behat.notification_accessor.admin'),
        ])
        ->tag('fob.context_service');

    $services->set(NotFoundLogIndexPageInterface::class, NotFoundLogIndexPage::class)
        ->parent('sylius.behat.page.admin.crud.index')
        ->private()
        ->args(['three_brs_sylius_404_log_plugin_admin_not_found_log_index']);

    $services->set(AggregatedLogIndexPageInterface::class, AggregatedLogIndexPage::class)
        ->parent('sylius.behat.symfony_page')
        ->private();

    $services->set(DetailsPageInterface::class, DetailsPage::class)
        ->parent('sylius.behat.symfony_page')
        ->private();
};
