<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 06.06.17
 * Time: 18:41
 */

namespace Scalar\IO\Factory;


use Scalar\IO\Stream\Stream;
use Scalar\IO\Stream\StreamInterface;

class StreamFactory implements StreamFactoryInterface
{

    /**
     * Create a new stream from a string.
     *
     * @param string $content
     * @return StreamInterface
     */
    public function createStream($content = '')
    {
        $stream = new Stream
        (
            fopen
            (
                'php://temp',
                'r+'
            )
        );
        $stream->write($content);
        return $stream;
    }

    /**
     * Create a stream from an existing file.
     *
     * @param string $filename
     * @param string $mode
     * @return StreamInterface
     */
    public function createStreamFromFile($filename, $mode = 'r')
    {

        $resource = @fopen
        (
            $filename,
            $mode
        );

        if ($resource) {
            return new Stream($resource);
        }

        return null;
    }

    /**
     * Create a new stream from an existing resource.
     *
     * @param resource $resource
     * @return StreamInterface
     */
    public function createStreamFromResource($resource)
    {
        return new Stream($resource);
    }
}