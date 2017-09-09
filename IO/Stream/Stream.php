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


use Scalar\IO\Exception\IOException;

class Stream implements StreamInterface
{

    private static $readWriteHash = [
        'read' => [
            'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true
        ],
        'write' => [
            'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true
        ]
    ];
    /**
     * @var resource
     */
    private $stream;
    /**
     * @var int
     */
    private $size;
    /**
     * @var bool
     */
    private $seekable;
    /**
     * @var bool
     */
    private $readable;
    /**
     * @var bool
     */
    private $writable;
    /**
     * @var array|mixed|null
     */
    private $uri;
    /**
     * @var array|mixed
     */
    private $customMetadata;

    public function __construct($stream, $options = [])
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('Stream must be a resource');
        }
        if (isset($options['size'])) {
            $this->size = $options['size'];
        }
        $this->customMetadata = isset($options['metadata'])
            ? $options['metadata']
            : [];
        $this->stream = $stream;
        $meta = stream_get_meta_data($this->stream);
        $this->seekable = $meta['seekable'];
        $this->readable = isset(self::$readWriteHash['read'][$meta['mode']]);
        $this->writable = isset(self::$readWriteHash['write'][$meta['mode']]);
        $this->uri = $this->getMetadata('uri');
    }

    /**
     * Get stream metadata
     * @param string $key
     * @return mixed|array|null
     */
    public function getMetadata($key = null)
    {
        if (!isset($this->stream)) {
            return $key ? null : [];
        } elseif (!$key) {
            return $this->customMetadata + stream_get_meta_data($this->stream);
        } elseif (isset($this->customMetadata[$key])) {
            return $this->customMetadata[$key];
        }
        $meta = stream_get_meta_data($this->stream);
        return isset($meta[$key]) ? $meta[$key] : null;
    }

    /**
     * Get stream size if known
     * @return int|null
     */
    public function getSize()
    {
        if ($this->size !== null) {
            return $this->size;
        }
        if (!isset($this->stream)) {
            return null;
        }
        if ($this->uri) {
            clearstatcache(true, $this->uri);
        }
        $stats = fstat($this->stream);
        if (isset($stats['size'])) {
            $this->size = $stats['size'];
            return $this->size;
        }
        return null;
    }

    /**
     * Get current pointer position
     * @return int
     * @throws IOException on error.
     */
    public function getPointerPosition()
    {
        $result = ftell($this->stream);
        if ($result === false) {
            throw new IOException('Unable to determine stream position');
        }
        return $result;
    }

    /**
     * Write to stream
     * @param string $string Data to be written
     * @return int Amount of bytes written
     * @throws IOException
     */
    public function write($string)
    {
        if (!$this->writable) {
            throw new IOException('Cannot write to a non-writable stream');
        }
        $this->size = null;
        $result = fwrite($this->stream, $string);
        if ($result === false) {
            throw new IOException('Unable to write to stream');
        }
        return $result;
    }

    /**
     * @param int $length Amount of bytes to read
     * @return string Data read from stream or empty
     * @throws IOException
     */
    public function read($length)
    {
        if ($this->atEof())
            return '';
        if (!$this->isReadable())
            throw new IOException("Stream is not readable");
        if ($length < 0)
            throw new IOException("Cannot read backwards");
        if (0 === $length) {
            return '';
        }
        $string = fread($this->stream, $length);
        if (false === $string) {
            throw new IOException('Unable to read from stream');
        }
        return $string;
    }

    /**
     * Check if pointer reached stream end
     * @return bool
     */
    public function atEof()
    {
        return !$this->stream || feof($this->stream);
    }

    /**
     * Check if data is readable
     * @return bool
     */
    public function isReadable()
    {
        return $this->readable;
    }

    public function wipe()
    {
        if (!$this->isReadable())
            throw new IOException("Stream is not readable");
        if (!$this->isWritable())
            throw new IOException("Stream is not writeable");

        if ($this->isSeekable())
            $this->rewind();
        return ftruncate($this->stream, 0);
    }

    /**
     * Check if stream is writable
     * @return bool
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * Check if stream is seekable
     * @return bool
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * Seek to the beginning of the stream
     * @throws \RuntimeException on error.
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * Seek pointer to stream position
     * @param int $offset
     * @param int $whence Calculation type
     * @throws IOException
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->isSeekable())
            throw new IOException("Cannot seek stream");
        elseif (fseek($this->stream, $offset, $whence) === -1) {
            throw new IOException('Unable to seek to stream position ' . $offset
                . ' with whence ' .
                var_export($whence, true));
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Close stream
     * @return void
     */
    public function close()
    {
        if (isset($this->stream)) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
            $this->detach();
        }
    }

    /**
     * Detach stream
     * @return resource|null
     */
    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }
        $result = $this->stream;
        unset($this->stream);
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;
        return $result;
    }

    /**
     * Read entire stream to string
     * @return string
     */
    public function __toString()
    {
        if ($this->isSeekable())
            $this->rewind();
        return $this->getContents();
    }

    /**
     * Get remaining contents in a string
     * @return string
     * @throws IOException
     */
    public function getContents()
    {
        $contents = stream_get_contents($this->stream);
        if ($contents === false) {
            throw new IOException('Unable to read stream content');
        }
        return $contents;
    }
}