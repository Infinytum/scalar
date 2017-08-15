<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 12.06.17
 * Time: 17:50
 */

namespace Scaly\Util\Annotation;


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