<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withSuite(
                (new Suite('ui_managing_404_logs'))
                    ->withPaths('features')
                    ->withContexts(
                        'sylius.behat.context.hook.doctrine_orm',
                        'sylius.behat.context.transform.country',
                        'sylius.behat.context.transform.customer',
                        'sylius.behat.context.transform.lexical',
                        'sylius.behat.context.transform.shared_storage',
                        'sylius.behat.context.setup.admin_security',
                        'sylius.behat.context.setup.channel',
                        'sylius.behat.context.setup.locale',
                        'tests.three_brs.sylius_404_log_plugin.behat.context.setup.not_found_log',
                        'sylius.behat.context.ui.admin.notification',
                        'tests.three_brs.sylius_404_log_plugin.behat.context.ui.admin.managing_404_logs',
                    ),
            ),
    );
