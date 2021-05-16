<?php


namespace Voltel\WebpackAssetsBundle\Service;


use Symfony\Component\HttpFoundation\UrlHelper;

class AssetsTagsPrintService
{
    /** @var EntrypointAssetsRegistryService */
    private $entrypointAssetsRegistryService;

    /** @var UrlHelper */
    private UrlHelper $urlHelper;


    public function __construct(
        UrlHelper $urlHelper,
        EntrypointAssetsRegistryService $entrypointAssetsRegistryService
    )
    {
        $this->entrypointAssetsRegistryService = $entrypointAssetsRegistryService;
        $this->urlHelper = $urlHelper;
    }


    /**
     * Prints absolute or relative URLs for CSS files with stylesheets
     * imported in entrypoint(s) from the first argument.
     * If the second argument is "true", the URLs will be absolute; otherwise - relative URLs.
     */
    public function printCssLinkTagsForEntries(array $a_entrypoints, bool $l_absolute_urls = false) : string
    {
        $a_tags = [];

        $a_relative_urls = $this->entrypointAssetsRegistryService->getCssAssetsRelativeUrlsForEntries($a_entrypoints);

        foreach ($a_relative_urls as $c_this_relative_url) {
            $a_tags[] = sprintf('<link rel="stylesheet" href="%s" />',
                $l_absolute_urls ? $this->urlHelper->getAbsoluteUrl($c_this_relative_url) :
                    $this->urlHelper->getRelativePath($c_this_relative_url));
        }

        return implode("\n", $a_tags);
    }

    /**
     * Prints absolute or relative URLs for JavaScript files
     * imported in entrypoint(s) from the argument one.
     * If argument two is "true", the URLs will be absolute.
     */
    public function printJsScriptTagsForEntries(array $a_entrypoints, bool $l_absolute_urls = false) : string
    {
        $a_tags = [];

        $a_relative_urls = $this->entrypointAssetsRegistryService->getJsAssetsRelativeUrlsForEntries($a_entrypoints);

        foreach ($a_relative_urls as $c_this_relative_url) {
            $a_tags[] = sprintf('<script src="%s" />',
                $l_absolute_urls ? $this->urlHelper->getAbsoluteUrl($c_this_relative_url) :
                    $this->urlHelper->getRelativePath($c_this_relative_url));
        }

        return implode("\n", $a_tags);
    }

}
