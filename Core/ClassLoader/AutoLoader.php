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

namespace Scalar\Core\ClassLoader;

class AutoLoader
{

    protected $prefixes = array();
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