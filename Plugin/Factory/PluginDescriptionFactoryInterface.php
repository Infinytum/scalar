<?php

namespace Scaly\Plugin\Factory;

use Scaly\Plugin\PluginDescriptionInterface;

interface PluginDescriptionFactoryInterface
{

    /**
     * Create instance of plugin description
     *
     * @param string|null $id
     * @param string|null $repository
     * @param string|null $name
     * @param string|null $version
     * @param int|null $packageVersion
     * @param string|null $author
     * @param string|null $description
     * @return PluginDescriptionInterface
     */
    public function createPluginDescription
    (
        $id = null,
        $repository = null,
        $name = null,
        $version = null,
        $packageVersion = null,
        $author = null,
        $description = null
    );

}