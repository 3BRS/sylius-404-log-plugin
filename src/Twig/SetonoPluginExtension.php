<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\Twig;

use ThreeBRS\Sylius404LogPlugin\Service\SetonoPluginDetector;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SetonoPluginExtension extends AbstractExtension
{
    private SetonoPluginDetector $pluginDetector;

    public function __construct(SetonoPluginDetector $pluginDetector)
    {
        $this->pluginDetector = $pluginDetector;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_setono_redirect_plugin_installed', [$this, 'isSetonoRedirectPluginInstalled']),
        ];
    }

    public function isSetonoRedirectPluginInstalled(): bool
    {
        return $this->pluginDetector->isSetonoRedirectPluginInstalled();
    }
}
