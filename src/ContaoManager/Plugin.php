<?php

namespace Clickpress\ContaoLogoCarousel\ContaoManager;

use Clickpress\ContaoLogoCarousel\ContaoLogoCarousel;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\CoreBundle\ContaoCoreBundle;

class Plugin implements BundlePluginInterface
{

    /**
     * @inheritDoc
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoLogoCarousel::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
