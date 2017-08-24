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

namespace Scalar\Util\Factory;

use Scalar\Util\Annotation\Annotation;

class AnnotationFactory
{

    /**
     * @var string
     */
    private $phpDocRegex = '/@(?<property>[A-Z][^\s]+)(?:\s){0,1}(?<values>.*)/';

    public function createAnnotation
    (
        $name,
        $arguments = []
    )
    {
        return new Annotation($name, $arguments);
    }

    public function createAnnotationFromArray
    (
        $array
    )
    {
        if (count($array) < 1) {
            return null;
        }
        $name = array_shift($array);
        return new Annotation($name, $array);

    }

    public function createAnnotationFromString
    (
        $string
    )
    {
        if (preg_match($this->phpDocRegex, $string, $match)) {

            $annotationName = $match["property"];
            $annotationValues = str_getcsv($match["values"], ' ');
            if (is_array($annotationValues) && count($annotationValues) == 1) {
                $annotationValues = $annotationValues[0];
            }
            if (is_array($annotationValues) && count($annotationValues) == 0) {
                $annotationValues = null;
            }

            return new Annotation
            (
                $annotationName,
                $annotationValues
            );
        }

        return null;
    }

    public function createAnnotationArrayFromString
    (
        $string
    )
    {
        if (preg_match_all($this->phpDocRegex, $string, $matches, PREG_SET_ORDER, 0)) {
            $annotations = [];
            foreach ($matches as $match) {
                $annotationName = $match["property"];
                $annotationValues = str_getcsv($match["values"], ' ');
                if (is_array($annotationValues) && count($annotationValues) == 1) {
                    $annotationValues = $annotationValues[0];
                }
                if (is_array($annotationValues) && count($annotationValues) == 0) {
                    $annotationValues = null;
                }

                array_push($annotations, new Annotation
                (
                    $annotationName,
                    $annotationValues
                ));
            }

            return $annotations;

        }

        return [];
    }

}