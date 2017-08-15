<?php

namespace Scaly\Config;

use Scaly\Core\Scaly;
use Scaly\IO\Factory\StreamFactory;
use Scaly\IO\Stream\Stream;
use Scaly\IO\Stream\StreamInterface;
use Scaly\Util\ScalyArray;

class JsonConfig implements ConfigInterface
{

    /**
     * @var Stream
     */
    private $fileStream;

    /**
     * @var ScalyArray
     */
    private $configArray;

    /**
     * JsonConfig constructor.
     * @param resource|Stream|string $fileLocation
     * @param ScalyArray|array $configArray
     */
    public function __construct
    (
        $fileLocation,
        $configArray = []
    )
    {
        $streamFactory = new StreamFactory();

        if (is_string($fileLocation)) {
            if (!file_exists($fileLocation)) {
                @mkdir(dirname($fileLocation), 0777, true);
                @touch($fileLocation);
                @chmod($fileLocation, 0777);
                Scaly::getLogger()->i
                (
                    'File not found. Creating new file...'
                );
            }
            $this->fileStream = $streamFactory->createStreamFromFile($fileLocation, "r+");
        } elseif (is_resource($fileLocation)) {
            $this->fileStream = $streamFactory->createStreamFromResource($fileLocation);
        } elseif ($fileLocation instanceof StreamInterface) {
            $this->fileStream = $fileLocation;
        } else {
            throw new \InvalidArgumentException
            (
                'Invalid file location passed to json config'
            );
        }

        if ($this->fileStream == null) {
            $this->fileStream = $streamFactory->createStream();
            Scaly::getLogger()->i
            (
                'Could not create file. Using in-memory json config! This may slow down performance.'
            );
        }

        if (is_array($configArray) && !$configArray instanceof ScalyArray) {
            $this->configArray = new ScalyArray($configArray);
        } elseif ($configArray instanceof ScalyArray) {
            $this->configArray = $configArray;
        } else {
            throw new \InvalidArgumentException
            (
                'Invalid config array passed to json config'
            );
        }
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
        if ($this->configArray->contains($key)) {
            return $this->configArray[$key];
        }
        return $default;
    }

    /**
     * Retrieve value stored in config
     *
     * @param $key
     * @param $default
     * @return mixed
     */
    public function getPath
    (
        $key,
        $default = null
    )
    {
        if ($this->configArray->containsPath($key)) {
            return $this->configArray->getPath($key);
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
            return;
        }
        $this->setDefault($key, $value);
        $this->save();
        $this->load();
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
        return $this->configArray->contains($key);
    }

    /**
     * Set default value in config if not present
     *
     * @param $key
     * @param $value
     * @return void
     */
    public function setDefault
    (
        $key,
        $value
    )
    {
        if ($this->has($key)) {
            return;
        }
        $this->set($key, $value);
    }

    /**
     * Set a config value
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set
    (
        $key,
        $value
    )
    {
        $this->configArray[$key] = $value;
    }

    /**
     * Save configuration
     *
     * @return void
     */
    public function save()
    {
        $this->fileStream->wipe();
        $this->fileStream->write
        (
            json_encode
            (
                $this->configArray->asArray(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );
    }

    /**
     * Load configuration
     *
     * @return void
     */
    public function load()
    {
        $this->fileStream->rewind();
        $array = json_decode
        (
            $this->fileStream->getContents(),
            true
        );


        if (!$array) {
            $array = [];
        }

        $this->configArray = new ScalyArray($array);
    }

    public function setDefaultAndSavePath
    (
        $key,
        $value
    )
    {
        if ($this->hasPath($key)) {
            return;
        }
        $this->setDefaultPath($key, $value);
        $this->save();
        $this->load();
    }

    /**
     * Check if the config contains this key
     *
     * @param $key
     * @return bool
     */
    public function hasPath
    (
        $key
    )
    {
        return $this->configArray->containsPath($key);
    }

    /**
     * Set default value in config if not present
     *
     * @param $key
     * @param $value
     * @return void
     */
    public function setDefaultPath
    (
        $key,
        $value
    )
    {
        if ($this->hasPath($key)) {
            return;
        }
        $this->setPath($key, $value);
    }

    /**
     * Set a config value
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setPath
    (
        $key,
        $value
    )
    {
        $this->configArray->setPath($key, $value);
    }

    /**
     * Get config map as Scaly Array
     *
     * @return ScalyArray
     */
    public function asScalyArray()
    {
        return clone $this->configArray;
    }
}