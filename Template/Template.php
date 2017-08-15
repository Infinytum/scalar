<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 7/10/17
 * Time: 4:57 PM
 */

namespace Scaly\Template;


use Scaly\IO\Factory\StreamFactory;
use Scaly\IO\Stream\StreamInterface;

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