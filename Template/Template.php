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
 * User: nila
 * Date: 7/10/17
 * Time: 4:57 PM
 */

namespace Scalar\Template;


use Scalar\IO\Factory\StreamFactory;
use Scalar\IO\Stream\StreamInterface;

class Template
{

    /**
     * @var StreamInterface
     */
    private $templateStream;

    /**
     * @var int
     */
    private $cursor = 0;

    /**
     * Template constructor.
     * @param string|resource|StreamInterface $template Template as string, resource or Stream
     * @param int $cursor Read-cursor position
     * @throws \RuntimeException Is thrown when an invalid template is passed to the constructor
     */
    public function __construct
    (
        $template,
        $cursor = 0
    )
    {

        if (is_string($template)) {
            $streamFactory = new StreamFactory();
            $this->templateStream = $streamFactory->createStream($template);
        } else if (is_resource($template)) {
            $streamFactory = new StreamFactory();
            $this->templateStream = $streamFactory->createStreamFromResource($template);
        } else if ($template instanceof StreamInterface) {
            $this->templateStream = $template;
        } else {
            throw new \RuntimeException
            (
                'Invalid template given'
            );
        }

        $this->cursor = $cursor;
    }


    /**
     * Get raw template
     *
     * @return string
     */
    public function getRawTemplate()
    {
        $this->templateStream->rewind();
        $return = $this->templateStream->getContents();
        $this->templateStream->seek($this->cursor);
        return $return;
    }

    public function setRawTemplate
    (
        $template
    )
    {
        if (is_string($template)) {
            $streamFactory = new StreamFactory();
            $this->templateStream = $streamFactory->createStream($template);
        } else if (is_resource($template)) {
            $streamFactory = new StreamFactory();
            $this->templateStream = $streamFactory->createStreamFromResource($template);
        } else if ($template instanceof StreamInterface) {
            $this->templateStream = $template;
        } else {
            throw new \RuntimeException
            (
                'Invalid template given'
            );
        }
    }

}