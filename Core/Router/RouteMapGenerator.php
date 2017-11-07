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

namespace Scalar\Core\Router;


use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class RouteMapGenerator
{

    /**
     * Controller PHPDoc Property regex
     * @var string
     */
    private static $phpDocRegex = '/@(?<property>[A-Z][a-zA-Z]+)(?:\s){0,1}(?<values>.*)/';

    public static function fromApp
    (
        $appHome = SCALAR_APP
    )
    {
        $classes = [];
        $routes = [];

        if (!file_exists($appHome)) {
            return $routes;
        }

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appHome . '/')) as $item) {
            if (strpos($item, '.php') < 1) {
                continue;
            }
            require_once $item;
            $ns = extract_namespace($item);
            array_push($classes, $ns . "\\" . pathinfo(basename($item), PATHINFO_FILENAME));
        }

        foreach ($classes as $item) {
            $controller = new \stdClass();
            $reflector = new \ReflectionClass($item);
            $controllerData = $reflector->getDocComment();
            preg_match_all(self::$phpDocRegex, $controllerData, $matches, PREG_SET_ORDER, 0);
            foreach ($matches as $match) {
                $property = $match["property"];
                $values = str_getcsv($match["values"], ' ');
                if ($property === 'Path') {
                    foreach ($values as $key => $val) {
                        $values[$key] = strtolower($val);
                    }
                }
                if (is_array($values) && count($values) == 1)
                    $values = $values[0];

                $controller->$property = $values;
            }

            if (!isset($controller->Path))
                $controller->Path = '/' . strtolower(str_replace('Controller', '', $reflector->getShortName()));

            if (is_array($controller->Path)) {
                foreach ($controller->Path as $item2) {
                    $controllerClone = clone $controller;
                    $controllerClone->Path = $item2;
                    $routes = $routes + self::generateMethodMap($reflector, $controllerClone, $item);
                }
            } else {
                $routes = $routes + self::generateMethodMap($reflector, $controller, $item);
            }


        }

        return $routes;
    }

    /**
     * @param \ReflectionClass $controllerReflect
     * @param $controller
     * @param $controllerName
     * @return array
     */
    private static function generateMethodMap($controllerReflect, $controller, $controllerName)
    {
        $routes = [];
        foreach ($controllerReflect->getMethods() as $phpMethod) {
            $method = new \stdClass();
            $method->Controller = $controllerName;
            $method->Function = $phpMethod->getName();
            if ($phpMethod->getName() == '__construct') {
                continue;
            }
            $methodData = $phpMethod->getDocComment();
            preg_match_all(self::$phpDocRegex, $methodData, $matches, PREG_SET_ORDER, 0);
            foreach ($matches as $match) {
                $property = $match["property"];
                $values = str_getcsv($match["values"], ' ');
                if ($property === 'Path') {
                    foreach ($values as $key => $val) {
                        $val = str_replace('${Controller}', $controller->Path, $val);
                        $values[$key] = strtolower($val);
                    }
                }
                if (is_array($values) && count($values) == 1)
                    $values = $values[0];
                $method->$property = $values;
            }

            if (!isset($method->Path))
                $method->Path = $controller->Path . '/' . lcfirst($phpMethod->getName());

            $routes[$method->Path] = ['Data' => $method];

        }
        return json_decode(json_encode($routes), true);
    }

}
