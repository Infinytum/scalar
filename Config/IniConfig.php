<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 05.06.17
 * Time: 21:40
 */

namespace Scalar\Config;


use Scalar\IO\Stream\Stream;
use Scalar\IO\Stream\StreamInterface;
use Scalar\Util\ScalarArray;

class IniConfig implements ConfigInterface
{

    /**
     * @var Stream
     */
    private $fileStream;

    /**
     * @var bool
     */
    private $sections;

    /**
     * @var ScalarArray
     */
    private $configArray;

    /**
     * @var int
     */
    private $iniScannerMode;

    /**
     * IniConfig constructor.
     * @param resource|Stream|string $fileLocation
     * @param ScalarArray|array $configArray
     * @param bool $sections
     * @param int $iniScannerMode
     */
    function __construct
    (
        $fileLocation,
        $configArray = [],
        $sections = true,
        $iniScannerMode = INI_SCANNER_TYPED
    )
    {
        if (is_string($fileLocation)) {
            if (!file_exists($fileLocation)) {
                @mkdir(dirname($fileLocation), 0777, true);
                @touch($fileLocation);
                @chmod($fileLocation, 0777);
            }
            $this->fileStream = new Stream(fopen($fileLocation, "r+"));
        } elseif (is_resource($fileLocation)) {
            $this->fileStream = new Stream($fileLocation);
        } elseif ($fileLocation instanceof StreamInterface) {
            $this->fileStream = $fileLocation;
        } else {
            throw new \InvalidArgumentException
            (
                'Invalid file location passed to ini config'
            );
        }

        if (is_array($configArray) && !$configArray instanceof ScalarArray) {
            $this->configArray = new ScalarArray($configArray);
        } elseif ($configArray instanceof ScalarArray) {
            $this->configArray = $configArray;
        } else {
            throw new \InvalidArgumentException
            (
                'Invalid config array passed to ini config'
            );
        }
        $this->sections = $sections;
        $this->iniScannerMode = $iniScannerMode;
    }

    /**
     * Retrieve value stored in config
     *
     * @param $key
     * @param $default
     * @return mixed
     */
    public function get
    (
        $key,
        $default = null
    )
    {
        if ($this->sections) {
            return $this->configArray->getPath($key);
        } elseif ($this->configArray->contains($key)) {
            return $this->configArray[$key];
        }
        return $default;
    }

    public function setDefaultAndSave
    (
        $key,
        $value
    )
    {
        if ($this->has($key)) {
            return $this;
        }
        $this->setDefault($key, $value);
        $this->save();
        $this->load();
        return $this;
    }

    /**
     * Check if the config contains this key
     *
     * @param $key
     * @return bool
     */
    public function has
    (
        $key
    )
    {
        if ($this->sections) {
            return $this->configArray->containsPath($key);
        } else {
            return $this->configArray->contains($key);
        }
    }

    /**
     * Set default value in config if not present
     *
     * @param $key
     * @param $value
     * @return static
     */
    public function setDefault
    (
        $key,
        $value
    )
    {
        if ($this->has($key)) {
            return $this;
        }
        $this->set($key, $value);
        return $this;
    }

    /**
     * Set a config value
     *
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public function set
    (
        $key,
        $value
    )
    {
        if ($this->sections) {
            $this->configArray->setPath($key, $value);
        } else {
            $this->configArray[$key] = $value;
        }
        return $this;
    }

    /**
     * Save configuration
     *
     * @return static
     */
    public function save()
    {
        $this->fileStream->wipe();
        if ($this->sections) {
            foreach ($this->configArray->asArray() as $section => $section_data) {
                $this->fileStream->write("[$section]" . PHP_EOL);
                foreach ($section_data as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $entry) {
                            $this->fileStream->write($key . "[] = \"$entry\"" . PHP_EOL);
                        }
                    } else {
                        if (is_bool($value))
                            $value = $value ? "true" : "false";
                        $this->fileStream->write("$key = \"$value\"" . PHP_EOL);
                    }
                }
            }
        } else {
            foreach ($this->configArray->asArray() as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $entry) {
                        $this->fileStream->write($key . "[] = \"$entry\"" . PHP_EOL);
                    }
                } else {
                    $this->fileStream->write("$key = \"$value\"" . PHP_EOL);
                }
            }
        }
        return $this;
    }

    /**
     * Load configuration
     *
     * @return static
     */
    public function load()
    {
        $this->fileStream->rewind();
        $this->configArray = new ScalarArray
        (
            parse_ini_string
            (
                $this->fileStream->getContents(),
                $this->sections,
                $this->iniScannerMode
            )
        );
        return $this;
    }

    /**
     * Get config map as Scalar Array
     *
     * @return ScalarArray
     */
    public function asScalarArray()
    {
        return clone $this->configArray;
    }
}