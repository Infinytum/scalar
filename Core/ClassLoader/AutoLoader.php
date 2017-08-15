<?php

namespace Scaly\Core\ClassLoader;

class AutoLoader
{


    /**
     * @var AutoLoader
     */
    protected static $instance;
    protected $prefixes = array();

    function __construct()
    {
        self::$instance = $this;
    }

    /**
     * @return AutoLoader
     */
    public static function getInstance(): AutoLoader
    {
        if (!self::$instance)
            new AutoLoader();
        return self::$instance;
    }

    /**
     * Register loader
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    public function inClassPath($className)
    {
        // TODO
    }

    public function addClassPath($namespace, $baseDirectory, $prepend = false)
    {
        $namespacePrefix = trim($namespace, '\\') . '\\';
        $baseDir = rtrim($baseDirectory, DIRECTORY_SEPARATOR) . '/';
        if (!isset($this->prefixes[$namespacePrefix])) {
            $this->prefixes[$namespacePrefix] = array();
        }

        if ($prepend) {
            array_unshift($this->prefixes[$namespacePrefix], $baseDir);
        } else {
            array_push($this->prefixes[$namespacePrefix], $baseDir);
        }
    }

    public function loadClass($class)
    {
        $prefix = $class;
        while (false !== $pos = strrpos($prefix, '\\')) {

            $prefix = substr($class, 0, $pos + 1);

            $relative_class = substr($class, $pos + 1);

            $mapped_file = $this->loadMappedClass($prefix, $relative_class);
            if ($mapped_file) {
                return $mapped_file;
            }
            $prefix = rtrim($prefix, '\\');
        }

        return false;
    }

    protected function loadMappedClass($namespace, $class)
    {
        if (isset($this->prefixes[$namespace]) === false) {
            return false;
        }

        foreach ($this->prefixes[$namespace] as $base_dir) {
            $file = $base_dir . str_replace('\\', '/', $class) . '.php';

            if ($this->loadFile($file)) {
                return $file;
            }
        }
        return false;
    }

    /**
     * Require file
     *
     * @param string $file absolute file path
     * @return bool True if file exists
     */
    protected function loadFile($file)
    {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }

}