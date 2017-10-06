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

namespace Scalar\IO;

use Scalar\IO\Factory\StreamFactory;
use Scalar\IO\Stream\StreamInterface;

/**
 * Class File
 *
 * Object oriented implementation of a file
 *
 * @package Scalar\IO
 */
class File
{

    /**
     * @var string $path
     */
    private $path;

    /**
     * @var bool $suppressWarnings
     */
    private $suppressWarnings;

    private $streamMode;

    /**
     * File constructor.
     * @param string $filePath Path to file
     * @param bool $suppressWarnings
     * @param string $streamMode
     */
    public function __construct
    (
        $filePath,
        $suppressWarnings = false,
        $streamMode = 'r+'
    )
    {
        $this->path = $filePath;
        $this->suppressWarnings = $suppressWarnings;
        $this->streamMode = $streamMode;
    }

    /**
     * Create file if not already existent
     *
     * @param int $chmod
     * @param bool $recursive
     * @return bool
     */
    public function createIfNotExists
    (
        $chmod = 0777,
        $recursive = false
    )
    {
        if ($this->exists()) {
            return $this->create
            (
                $chmod,
                $recursive
            );
        }
        return true;
    }

    /**
     * Check if file exists
     *
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->path);
    }

    /**
     * Check if file is writable
     *
     * @return bool
     */
    public function isWritable()
    {
        return is_writable($this->path);
    }

    /**
     * Check if file is readable
     *
     * @return bool
     */
    public function isReadable()
    {
        return is_readable($this->path);
    }

    /**
     * Create file
     *
     * @param int $chmod
     * @param bool $recursive
     * @return bool
     */
    public function create
    (
        $chmod = 0777,
        $recursive = false
    )
    {
        if ($this->suppressWarnings) {
            if ($recursive) {
                if (!file_exists(dirname($this->path))) {
                    @mkdir(dirname($this->path), $chmod, $recursive);
                }
            }
            $retVal = @touch($this->path);
        } else {
            if ($recursive) {
                if (!file_exists(dirname($this->path))) {
                    mkdir(dirname($this->path), $chmod, $recursive);
                }
            }
            $retVal = touch($this->path);
        }
        $this->chmod($chmod);
        return $retVal;
    }

    /**
     * Change file permissions. This only works on UNIX like systems.
     *
     * @param int $mode
     * @return bool
     */
    public function chmod
    (
        $mode
    )
    {
        if ($this->suppressWarnings) {
            return @chmod($this->path, $mode);
        } else {
            return chmod($this->path, $mode);
        }
    }

    /**
     * Move file to new location
     *
     * @param $path
     * @return bool
     */
    public function move
    (
        $path
    )
    {
        if ($this->suppressWarnings) {
            return @rename($this->path, $path);
        } else {
            return rename($this->path, $path);
        }
    }

    /**
     * Permanently delete file
     */
    public function delete()
    {
        unlink($this->path);
    }

    /**
     * Get a stream pointing to this file
     *
     * @param string $mode
     * @return StreamInterface
     */
    public function toStream
    (
        $mode = null
    )
    {
        if (!$mode) {
            $mode = $this->streamMode;
        }

        $streamFactory = new StreamFactory();
        return $streamFactory->createStreamFromFile
        (
            $this->path,
            $mode
        );
    }

}