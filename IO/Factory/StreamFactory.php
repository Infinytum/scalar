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