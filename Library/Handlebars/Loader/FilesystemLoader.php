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

/**
 *
 * @category  Xamin
 * @package   Handlebars
 * @author    fzerorubigd <fzerorubigd@gmail.com>
 * @author    Behrooz Shabani <everplays@gmail.com>
 * @author    Craig Bass <craig@clearbooks.co.uk>
 * @author    ^^         <craig@devls.co.uk>
 * @copyright 2012 (c) ParsPooyesh Co
 * @copyright 2013 (c) Behrooz Shabani
 * @license   MIT
 * @link      http://voodoophp.org/docs/handlebars
 */

namespace Handlebars\Loader;

use Handlebars\HandlebarsString;
use Handlebars\Loader;


class FilesystemLoader implements Loader
{
    private $_baseDir;
    private $_extension = '.handlebars';
    private $_prefix = '';
    private $_templates = array();

    /**
     * Handlebars filesystem Loader constructor.
     *
     * $options array allows overriding certain Loader options during instantiation:
     *
     *     $options = array(
     *         // extension used for Handlebars templates. Defaults to '.handlebars'
     *         'extension' => '.other',
     *     );
     *
     * @param string|array $baseDirs A path contain template files or array of paths
     * @param array $options Array of Loader options (default: array())
     *
     * @throws \RuntimeException if $baseDir does not exist.
     */
    public function __construct($baseDirs, Array $options = [])
    {
        $initialBaseDirs = $baseDirs;
        if (is_string($baseDirs)) {
            $realBaseDir = rtrim(realpath($baseDirs), '/');
            $baseDirs = $realBaseDir == "" ? array() : array($realBaseDir);
        } else {
            $newBaseDirs = array();
            foreach ($baseDirs as $dir) {
                $dir = rtrim(realpath($dir), '/');
                if($dir != "") {
                    $newBaseDirs = array_push($newBaseDirs, $dir);
                }
            }
            $baseDirs = $newBaseDirs;
        }

        $this->_baseDir = $baseDirs;
        foreach ($this->_baseDir as $dir) {
            if (!file_exists($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    throw new \RuntimeException(
                        'Template Engine was unable to create resources directory: ' . $dir
                    );
                }
            }
            if (!is_dir($dir)) {
                throw new \RuntimeException(
                    'FilesystemLoader baseDir must be a directory: ' . $dir
                );
            }
        }

        if (count($baseDirs) == 0) {
            throw new \RuntimeException(
                'Template Engine was unable to find a single valid template directory: ' . (is_string($initialBaseDirs) ? $initialBaseDirs : json_encode($initialBaseDirs))
            );
        }

        if (isset($options['extension'])) {
            $this->_extension = '.' . ltrim($options['extension'], '.');
        }

        if (isset($options['prefix'])) {
            $this->_prefix = $options['prefix'];
        }
    }

    public function addBaseDir
    (
        $baseDirPath
    )
    {
        array_push($this->_baseDir, $baseDirPath);
    }

    /**
     * Load a Template by name.
     *
     *     $loader = new FilesystemLoader(dirname(__FILE__).'/views');
     *     // loads "./views/admin/dashboard.handlebars";
     *     $loader->load('admin/dashboard');
     *
     * @param string $name template name
     *
     * @return HandlebarsString Handlebars Template source
     */
    public function load($name)
    {
        if (!isset($this->_templates[$name])) {
            $this->_templates[$name] = $this->loadFile($name);
        }

        return new HandlebarsString($this->_templates[$name]);
    }

    /**
     * Helper function for loading a Handlebars file by name.
     *
     * @param string $name template name
     *
     * @throws \InvalidArgumentException if a template file is not found.
     * @return string Handlebars Template source
     */
    protected function loadFile($name)
    {
        $fileName = $this->getFileName($name);

        if ($fileName === false) {
            throw new \InvalidArgumentException('Template ' . $name . ' not found.');
        }

        return file_get_contents($fileName);
    }

    /**
     * Helper function for getting a Handlebars template file name.
     *
     * @param string $name template name
     *
     * @return string Template file name
     */
    protected function getFileName($name)
    {
        foreach ($this->_baseDir as $baseDir) {
            $fileName = $baseDir . '/';
            $fileParts = explode('/', $name);
            $file = array_pop($fileParts);

            if (substr($file, strlen($this->_prefix)) !== $this->_prefix) {
                $file = $this->_prefix . $file;
            }

            $fileParts[] = $file;
            $fileName .= implode('/', $fileParts);
            $lastCharacters = substr($fileName, 0 - strlen($this->_extension));

            if ($lastCharacters !== $this->_extension) {
                $fileName .= $this->_extension;
            }
            if (file_exists($fileName)) {
                return $fileName;
            }
        }

        return false;
    }

}
