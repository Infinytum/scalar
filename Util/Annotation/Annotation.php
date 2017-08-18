<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 12.06.17
 * Time: 17:52
 */

namespace Scalar\Util\Annotation;


class Annotation
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $arguments;

    public function __construct
    (
        $name,
        $arguments
    )
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }


}