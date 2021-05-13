<?php

namespace Voltel\WebpackAssetsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Voltel\WebpackAssetsBundle\DependencyInjection\VoltelWebpackAssetsExtension;

class VoltelWebpackAssetsBundle extends Bundle
{

    /**
     * Read at: https://symfony.com/doc/current/bundles/extension.html#manually-registering-an-extension-class
     */
    public function getContainerExtension()
    {
        if (is_null($this->extension)) {
            $this->extension = new VoltelWebpackAssetsExtension();
        }
        return $this->extension;
    }

}
