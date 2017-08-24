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

namespace Scalar\IO\Stream;

/**
 * Interface StreamInterface
 *
 * A wrapper around a data stream
 *
 * @package Scalar\IO\Stream
 */
interface StreamInterface
{

    /**
     * Read entire stream to string
     * @return string
     */
    public function __toString();

    /**
     * Close stream
     * @return void
     */
    public function close();

    /**
     * Detach stream
     * @return resource|null
     */
    public function detach();

    /**
     * Get stream size if known
     * @return int|null
     */
    public function getSize();

    /**
     * Get current pointer position
     * @return int
     * @throws \RuntimeException on error.
     */
    public function getPointerPosition();

    /**
     * Check if pointer reached stream end
     * @return bool
     */
    public function atEof();

    /**
     * Check if stream is seekable
     * @return bool
     */
    public function isSeekable();

    /**
     * Seek pointer to stream position
     * @param int $offset
     * @param int $whence Calculation type
     * @throws \RuntimeException on error.
     */
    public function seek($offset, $whence = SEEK_SET);

    /**
     * Seek to the beginning of the stream
     * @throws \RuntimeException on error.
     */
    public function rewind();

    /**
     * Check if stream is writable
     * @return bool
     */
    public function isWritable();

    /**
     * Write to stream
     * @param string $string Data to be written
     * @return int Amount of bytes written
     * @throws \RuntimeException on error.
     */
    public function write($string);

    /**
     * Check if data is readable
     * @return bool
     */
    public function isReadable();

    /**
     * @param int $length Amount of bytes to read
     * @return string Data read from stream or empty
     * @throws \RuntimeException on error.
     */
    public function read($length);

    /**
     * Get remaining contents in a string
     * @return string
     * @throws \RuntimeException on error.
     */
    public function getContents();

    /**
     * Get stream metadata
     * @param string $key
     * @return mixed|array|null
     */
    public function getMetadata($key = null);
}