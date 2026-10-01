<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Filter\TagFilter;
use Behat\Config\GherkinOptions;
use Behat\Config\Profile;
use Behat\Config\TesterOptions;
use Behat\MinkExtension\ServiceContainer\MinkExtension;
use FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension;
use FriendsOfBehat\VariadicExtension\ServiceContainer\VariadicExtension;
use Tests\ThreeBRS\Sylius404LogPlugin\Kernel;

return (new Config())
    ->import('tests/Behat/Resources/suites.php')
    ->withProfile(
        (new Profile('default'))
            ->withGherkinOptions((new GherkinOptions())->withFilter(new TagFilter('~@todo&&~@cli')))
            ->withTesterOptions((new TesterOptions())->withErrorReporting(\E_ALL & ~(\E_DEPRECATED | \E_USER_DEPRECATED)))
            ->withExtension(new Extension(MinkExtension::class, [
                'base_url' => 'http://127.0.0.1',
                'default_session' => 'symfony',
                'sessions' => [
                    'symfony' => [
                        'symfony' => null,
                    ],
                ],
                'show_auto' => false,
            ]))
            ->withExtension(new Extension(SymfonyExtension::class, [
                'bootstrap' => 'tests/Application/config/bootstrap.php',
                'kernel' => [
                    'class' => Kernel::class,
                    'path' => 'tests/Application/Kernel.php',
                    'environment' => 'test',
                    'debug' => true,
                ],
            ]))
            ->withExtension(new Extension(VariadicExtension::class)),
    );
