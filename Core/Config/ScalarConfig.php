<?php

namespace Scalar\Core\Config;


use Scalar\Config\IniConfig;
use Scalar\Util\ScalarArray;

class ScalarConfig extends IniConfig
{

    private static $instance;

    private $overrides;

    private $injectableRegex = '/{{(?<Path>[^}]*)}}/x';

    function __construct()
    {
        parent::__construct(SCALAR_CORE . '/config.ini', []);
        $this->load();
        self::$instance = $this;
        $this->overrides = new ScalarArray([]);
    }

    /**
     * Get singleton
     *
     * @return ScalarConfig
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            new ScalarConfig();
        }
        return self::$instance;
    }

    public function get
    (
        $key,
        $default = null,
        $placeholder = true
    )
    {
        $result = parent::get($key, $default);
        if (is_string($result) && $placeholder) {
            preg_match_all
            (
                $this->injectableRegex,
                $result,
                $injectables,
                PREG_SET_ORDER,
                0
            );


            foreach ($injectables as $injectable) {
                $path = $injectable['Path'];
                if ($this->overrides->containsPath($path)) {
                    $result = str_replace($injectable[0], $this->overrides->getPath($path), $result);
                } else if ($this->has($path)) {
                    $result = str_replace($injectable[0], $this->get($path), $result);
                }
            }

        }
        return $result;
    }

    public function addOverride($path, $value)
    {
        $this->overrides->setPath($path, $value);
    }
}