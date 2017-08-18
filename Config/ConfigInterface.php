<?php

namespace Scalar\Config;


interface ConfigInterface
{

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
    );

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
    );

    /**
     * Check if the config contains this key
     *
     * @param $key
     * @return bool
     */
    public function has
    (
        $key
    );

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
        $default
    );

    /**
     * Load configuration
     *
     * @return void
     */
    public function load();

    /**
     * Save configuration
     *
     * @return void
     */
    public function save();
}