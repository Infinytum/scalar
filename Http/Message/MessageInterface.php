<?php

namespace Scaly\Http\Message;

use Scaly\IO\Stream\StreamInterface;

/**
 * Interface MessageInterface
 *
 * Abstract HTTP protocol implementation
 *
 * @package Scaly\Http\Message
 */
interface MessageInterface
{
    /**
     * Get protocol version
     * @return string protocol version.
     */
    public function getProtocolVersion();

    /**
     * Get instance with protocol version
     * @param string $version protocol version
     * @return static
     */
    public function withProtocolVersion($version);

    /**
     * Get all headers
     * @return string[][] Returns an associative array
     */
    public function getHeaders();

    /**
     * Check if header is present
     * @param string $name header name.
     * @return bool
     */
    public function hasHeader($name);

    /**
     * Get header values as array
     * @param string $name header name.
     * @return string[] An array of string values
     */
    public function getHeader($name);

    /**
     * Get header value as concatenated string
     * @param string $name header name.
     * @return string Concatenated header value
     */
    public function getHeaderLine($name);

    /**
     * Get instance with header
     * @param string $name header name.
     * @param string|string[] $value Header value.
     * @return static
     */
    public function withHeader($name, $value);

    /**
     * Get message with added header
     * @param string $name header to add.
     * @param string|string[] $value Header value.
     * @return static
     */
    public function withAddedHeader($name, $value);

    /**
     * Get message without header
     * @param string $name header to remove.
     * @return static
     */
    public function withoutHeader($name);

    /**
     * Get body of message
     *
     * @return StreamInterface Message body
     */
    public function getBody();

    /**
     * Get message with body
     * @param StreamInterface $body Body.
     * @return static
     */
    public function withBody($body);
}