<?php

namespace Scaly\Http\Message;


/**
 * Interface ResponseInterface
 *
 * Abstract server response implementation
 *
 * @package Scaly\Http\Message
 */
interface ResponseInterface extends MessageInterface
{
    /**
     * Get response code
     * @return int Status code.
     */
    public function getStatusCode();

    /**
     * Get instance with status code
     * @param int $code
     * @param string $reason
     * @return static
     */
    public function withStatus($code, $reason = '');

    /**
     * Get response reason
     * @return string Reason
     */
    public function getReasonPhrase();

    /**
     * Get all CustomArguments
     * @return string[][] Returns an associative array
     */
    public function getCustomArguments();

    /**
     * Check if CustomArgument is present
     * @param string $name CustomArgument name.
     * @return bool
     */
    public function hasCustomArgument($name);

    /**
     * Get CustomArgument values as array
     * @param string $name CustomArgument name.
     * @return string[] An array of string values
     */
    public function getCustomArgument($name);

    /**
     * Get instance with CustomArgument
     * @param string $name CustomArgument name.
     * @param string|string[] $value CustomArgument value.
     * @return static
     */
    public function withCustomArgument($name, $value);

    /**
     * Get message with added CustomArgument
     * @param string $name CustomArgument to add.
     * @param string|string[] $value CustomArgument value.
     * @return static
     */
    public function withAddedCustomArgument($name, $value);

    /**
     * Get message without CustomArgument
     * @param string $name CustomArgument to remove.
     * @return static
     */
    public function withoutCustomArgument($name);
}