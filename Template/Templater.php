<?php
/**
 * (C) 2017 by Michael Teuscher (mk.teuscher@gmail.com)
 * as part of the Scalar PHP framework
 *
 * Released under the AGPL v3.0 license
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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