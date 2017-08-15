<?php

namespace Scaly\IO\Stream;

/**
 * Interface StreamInterface
 *
 * A wrapper around a data stream
 *
 * @package Scaly\IO\Stream
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