<?php

namespace Scaly\Http\Message;

use Scaly\IO\UriInterface;

/**
 * Interface RequestInterface
 *
 * Abstract HTTP request implementation
 *
 * @package Scaly\Http\Message
 */
interface RequestInterface extends MessageInterface
{
    /**
     * Get request target
     * @return string
     */
    public function getRequestTarget();

    /**
     * Get instance with request target
     * @param mixed $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget);

    /**
     * Get request method
     * @return string
     */
    public function getMethod();

    /**
     * Get instance with request method
     * @param string $method
     * @return static
     */
    public function withMethod($method);

    /**
     * Get request URI
     * @return UriInterface
     */
    public function getUri();

    /**
     * Get instance with URI
     * @param UriInterface $uri
     * @param bool $preserveHost
     * @return static
     */
    public function withUri($uri, $preserveHost = false);
}