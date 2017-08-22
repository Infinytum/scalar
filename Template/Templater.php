<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 7/11/17
 * Time: 2:45 PM
 */

namespace Scalar\Template;

use Scalar\Core\Scalar;

class Templater
{

    private static $instance;

    private $cache;

    public function __construct()
    {
        self::$instance = $this;
        if (Scalar::getServiceMap()->hasService(Scalar::SERVICE_MEM_CACHE)) {
            $this->cache = Scalar::getService
            (
                Scalar::SERVICE_MEM_CACHE
            );
        } else {
            $this->cache = Scalar::getService
            (
                Scalar::SERVICE_FILE_CACHE
            );
        }
    }

    /**
     * @deprecated
     * @return Templater
     */
    public static function getInstance()
    {
        return Scalar::getService
        (
            Scalar::SERVICE_TEMPLATER
        );
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
        $scalarConfig = Scalar::getService
        (
            Scalar::SERVICE_SCALAR_CONFIG
        );

        $file = $scalarConfig->get("Template.Location") . $template . '.scalar';
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