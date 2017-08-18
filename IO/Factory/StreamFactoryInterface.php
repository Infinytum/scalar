<?php

namespace Scalar\IO\Factory;


use Scalar\IO\Stream\StreamInterface;

interface StreamFactoryInterface
{

    /**
     * Create a new stream from a string.
     *
     * @param string $content
     * @return StreamInterface
     */
    public function createStream($content = '');

    /**
     * Create a stream from an existing file.
     *
     * @param string $filename
     * @param string $mode
     * @return StreamInterface
     */
    public function createStreamFromFile($filename, $mode = 'r');

    /**
     * Create a new stream from an existing resource.
     *
     * @param resource $resource
     * @return StreamInterface
     */
    public function createStreamFromResource($resource);

}