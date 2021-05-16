<?php

namespace Voltel\WebpackAssetsBundle\Service;

use Symfony\Component\HttpFoundation\UrlHelper;
use Twig\Extension\RuntimeExtensionInterface;

class WebpackEntryRuntime implements RuntimeExtensionInterface
{
    /** @var EntrypointAssetsRegistryService */
    private $entrypointAssetsRegistryService;

    /** @var AssetsTagsPrintService */
    private $assetsTagsPrintService;

    public function __construct(
        EntrypointAssetsRegistryService $entrypointAssetsRegistryService,
        AssetsTagsPrintService $assetsTagsPrintService
    )
    {
        $this->entrypointAssetsRegistryService = $entrypointAssetsRegistryService;
        $this->assetsTagsPrintService = $assetsTagsPrintService;
    }

    /**
     * USAGE:
     * entry_css_source("homepage")
     * entry_css_source("common_layout", "homepage")
     * entry_css_source(["common_layout", "homepage"])
     *
     * @param string|array<string> $a_entrypoints
     * @return string
     * @throws \JsonException
     */
    public function getCssFileContentStringForEntries(...$a_entrypoints) : string
    {
        if (empty($a_entrypoints) || empty($a_entrypoints[0])) {
            throw new \LogicException(sprintf('voltel: Did you forget to provide one or more webpack entrypoint names or an array of entrypoint names for custom Twig Function "entry_css_source" to import css from?'));
        }//endif

        return $this->entrypointAssetsRegistryService->getCssAssetsFileContentForEntry(...$a_entrypoints);
    }

    /**
     * USAGE:
     * entry_css_urls("homepage")
     * entry_css_urls(["common_layout", "homepage"])
     *
     * @param mixed $mix_entry_points
     * @return array
     * @throws \JsonException
     */
    public function getCssAssetsRelativeUrlsForEntries($mix_entry_points) : array
    {
        if (empty($mix_entry_points)) {
            throw new \LogicException(sprintf('voltel: Did you forget to provide webpack entrypoint name or an array of names for custom Twig Function "entry_css_urls"?'));
        }//endif

        return $this->entrypointAssetsRegistryService->getCssAssetsRelativeUrlsForEntries($mix_entry_points);
    }


    /**
     * USAGE:
     * entry_js_urls("homepage")
     * entry_js_urls(["common_layout", "homepage"])
     *
     * @param mixed $mix_entry_points
     * @return array
     * @throws \JsonException
     */
    public function getJsAssetsRelativeUrlsForEntries($mix_entry_points) : array
    {
        if (empty($mix_entry_points)) {
            throw new \LogicException(sprintf('voltel: Did you forget to provide webpack entrypoint name or an array of names for custom Twig Function "entry_js_urls"?'));
        }//endif

        return $this->entrypointAssetsRegistryService->getJsAssetsRelativeUrlsForEntries($mix_entry_points);
    }


    /**
     * USAGE:
     * print_css_link_tags('homepage')
     * print_css_link_tags(['homepage'])
     * print_css_link_tags(['common_layout', 'homepage'])
     * print_css_link_tags('homepage', true)
     *
     * @param string|array<string> $mix_entrypoints
     * @param bool $l_absolute_urls
     * @return string
     */
    public function printCssLinkTagsForEntries($mix_entrypoints, bool $l_absolute_urls = false) : string
    {
        if (empty($mix_entry_points)) {
            throw new \LogicException(sprintf('voltel: Did you forget to provide webpack entrypoint name or an array of names for custom Twig Function "print_css_link_tags"?'));
        }//endif

        return $this->assetsTagsPrintService->printCssLinkTagsForEntries((array) $mix_entry_points, $l_absolute_urls);
    }


    /**
     * USAGE:
     * print_js_script_tags('homepage')
     * print_js_script_tags(['homepage'])
     * print_js_script_tags(['common_layout', 'homepage'])
     * print_js_script_tags('homepage', true)
     *
     * @param string|array<string> $mix_entrypoints
     * @param bool $l_absolute_urls
     * @return string
     */
    public function printJsScriptTagsForEntries($mix_entrypoints, bool $l_absolute_urls = false) : string
    {
        if (empty($mix_entry_points )) {
            throw new \LogicException(sprintf('voltel: Did you forget to provide webpack entrypoint name or an array of names for custom Twig Function "print_js_script_tags"?'));
        }//endif

        return $this->assetsTagsPrintService->printCssLinkTagsForEntries((array) $mix_entry_points, $l_absolute_urls);
    }

}//end of class
