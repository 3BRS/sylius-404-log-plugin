<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\Service;

use Symfony\Component\HttpKernel\KernelInterface;

class SetonoPluginDetector
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function isSetonoRedirectPluginInstalled(): bool
    {
        // Kontrola 1: Je bundle zaregistrován?
        $bundles = $this->kernel->getBundles();
        foreach ($bundles as $bundle) {
            if (str_contains(get_class($bundle), 'SetonoSyliusRedirectPlugin')) {
                return true;
            }
        }

        // Kontrola 2: Existuje třída?
        if (class_exists('Setono\SyliusRedirectPlugin\SetonoSyliusRedirectPlugin')) {
            return true;
        }

        return false;
    }
}
