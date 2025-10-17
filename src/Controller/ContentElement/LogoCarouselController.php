<?php

namespace Clickpress\ContaoLogoCarousel\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Filesystem\FilesystemItem;
use Contao\CoreBundle\Filesystem\FilesystemUtil;
use Contao\CoreBundle\Filesystem\SortMode;
use Contao\CoreBundle\Filesystem\VirtualFilesystem;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(category: 'miscellaneous', label: 'Infinite Scroller')]
class LogoCarouselController extends AbstractContentElementController
{

    public function __construct(
        private readonly VirtualFilesystem $filesStorage,
        private readonly Studio $studio,
        private readonly array $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        private readonly RequestStack $requestStack,
        private readonly ScopeMatcher $scopeMatcher,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        if (!$this->scopeMatcher->isBackendRequest($this->requestStack->getCurrentRequest())) {
            $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaologocarousel/logocarousel.js|async|static';
        }

        $GLOBALS['TL_CSS'][] = 'bundles/contaologocarousel/logocarousel.css|static';

        $filesystemItems = FilesystemUtil::listContentsFromSerialized($this->filesStorage, $this->getSources($model))
            ->filter(fn($item) => \in_array($item->getExtension(true), $this->validExtensions, true));

        // Sort elements; relay to client-side logic if list should be randomized
        if ($sortMode = SortMode::tryFrom($model->sortBy)) {
            $filesystemItems = $filesystemItems->sort($sortMode);
        }

        // Compile list of images
        $figureBuilder = $this->studio
            ->createFigureBuilder()
            ->setSize($model->size)
            ->setLightboxGroupIdentifier('lb' . $model->id)
            ->enableLightbox($model->fullsize);

        $imageList = array_filter(
            array_map(
                fn(FilesystemItem $filesystemItem): Figure|null => $figureBuilder
                    ->fromStorage($this->filesStorage, $filesystemItem->getPath())
                    ->buildIfResourceExists(),
                iterator_to_array($filesystemItems),
            )
        );

        if (!$imageList) {
            return new Response();
        }

        $template->set('images', $imageList);

        return $template->getResponse();
    }

    /**
     * @return string|array<string>
     */
    private function getSources(ContentModel $model): array|string
    {
        if ('image' === $model->type) {
            return [$model->singleSRC];
        }

        return $model->multiSRC ?? [];
    }
}
