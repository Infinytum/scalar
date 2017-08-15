<?php

namespace Scaly\Core\Config;


use Scaly\Config\IniConfig;
use Scaly\Util\ScalyArray;

class ScalyConfig extends IniConfig
{

    private static $instance;

    private $overrides;

    private $injectableRegex = '/{{(?<Path>[^}]*)}}/x';

    function __construct()
    {
        parent::__construct(SCALY_CORE . '/config.ini', []);
        $this->load();
        self::$instance = $this;
        $this->overrides = new ScalyArray([]);
    }

    /**
     * Get singleton
     *
     * @return ScalyConfig
     */
    public static function getInstance(): ScalyConfig
    {
        if (!self::$instance) {
            new ScalyConfig();
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