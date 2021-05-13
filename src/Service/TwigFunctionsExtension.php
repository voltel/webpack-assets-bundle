<?php


namespace Voltel\WebpackAssetsBundle\Service;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigFunctionsExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('entry_css_source', [WebpackEntryRuntime::class, 'getCssFileContentStringForEntries']),
            new TwigFunction('entry_css_urls', [WebpackEntryRuntime::class, 'getCssAssetsRelativeUrlsForEntries']),
            new TwigFunction('entry_js_urls', [WebpackEntryRuntime::class, 'getJsAssetsRelativeUrlsForEntries']),
        ];
    }

}
