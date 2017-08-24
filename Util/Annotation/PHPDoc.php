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
 * Date: 12.06.17
 * Time: 17:50
 */

namespace Scalar\Util\Annotation;


class PHPDoc
{

    /**
     * @var string
     */
    private $phpDocRegex = '/@(?<property>[A-Z][a-zA-Z]+)(?U)(\s){0,1}(?-U)(?<values>.*)/';

    /**
     * @var \ReflectionObject
     */
    private $reflectionObject = null;

    /**
     * PHPDoc constructor.
     * @param $reflectionObject
     */
    public function __construct
    (
        $reflectionObject
    )
    {
        $this->reflectionObject = $reflectionObject;
    }

    public function getAnnotations()
    {
        preg_match_all
        (
            $this->phpDocRegex,
            $this->reflectionObject->getDocComment(),
            $matches,
            PREG_SET_ORDER,
            0
        );

        $properties = [];

        foreach ($matches as $match) {
            $propertyName = $match['property'];
            $propertyValue = trim($match['values']);
            $propertyValue = str_getcsv($propertyValue, ' ');

            if (is_array($propertyValue) && count($propertyValue) == 1)
                $propertyValue = $propertyValue[0];

            if ($propertyValue === null) {
                $propertyValue = true;
            }

            $properties[$propertyName] = $propertyValue;
        }

        return $properties;

    }

}