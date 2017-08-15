<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 7/11/17
 * Time: 2:45 PM
 */

namespace Scaly\Template;


use Scaly\Cache\Cache;
use Scaly\Cache\Factory\FileCacheStorageFactory;
use Scaly\Cache\Factory\MemCacheStorageFactory;
use Scaly\Cache\Storage\MemCacheStorage;
use Scaly\Core\Config\ScalyConfig;

class Templater
{

    private static $instance;

    private $cache;

    public function __construct()
    {
        self::$instance = $this;
        if (MemCacheStorage::isAvailable()) {
            $memCacheStorageFactory = new MemCacheStorageFactory();
            $this->cache = new Cache($memCacheStorageFactory->createMemCacheStorage());
        } else {
            $fileStorageFactory = new FileCacheStorageFactory();
            $this->cache = new Cache($fileStorageFactory->createFileCacheStorage());
        }
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            new Templater();
        }
        return self::$instance;
    }

    public function buildFullTemplate
    (
        $template
    )
    {

        $template = $this->getTemplate($template);
        if (!$template)
            return null;

        $renderEngine = new \Mustache_Engine;

        $data = array_merge(ViewBag::getArray(), [
            "extends" => function (
                $text
            ) use ($template) {
                $parent = $this->buildFullTemplate($text);
                if ($parent)
                    $template->setRawTemplate($this->injectTemplate($template, $parent)->getRawTemplate());
                return "";
            }
        ]);

        $renderEngine->render($template->getRawTemplate(), $data);
        return $template;
    }

    /**
     * Get an template instance from the given template
     *
     * @param string $template Template Identifier
     * @return null|Template
     */
    public function getTemplate
    (
        $template
    )
    {
        $file = ScalyConfig::getInstance()->get("Template.Location") . $template . '.scaly';
        if (file_exists($file)) {
            return new Template(file_get_contents($file));
        }
        return null;
    }

    /**
     * @param $template Template
     * @param $parentTemplate Template
     * @return Template
     */
    private function injectTemplate
    (
        $template,
        $parentTemplate
    )
    {
        $renderEngine = new \Mustache_Engine;

        $data = array_merge(ViewBag::getArray(), [
            'SubView' => $template->getRawTemplate()
        ]);

        $rendered = $renderEngine->render($parentTemplate->getRawTemplate(),
            $data
        );
        return new Template($rendered);
    }

    /**
     * @param $template Template
     * @param mixed $data
     * @return string
     */
    public function renderTemplate
    (
        $template,
        $data = null
    )
    {
        if ($data == null) {
            $data = ViewBag::getArray();
        }

        $renderEngine = new \Mustache_Engine;
        return $renderEngine->render($template->getRawTemplate(), $data);
    }

    /**
     * @param string $template Template Identifier
     */
    public function hasTemplate
    (
        $template
    )
    {

    }
}