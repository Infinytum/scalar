<?php

namespace Scaly\Http\Message;

/**
 * Interface ServerRequestInterface
 *
 * Abstract Server Request Implementation
 *
 * @package Scaly\Http\Message
 */
interface ServerRequestInterface extends RequestInterface
{
    /**
     * Get server parameters
     * @return array
     */
    public function getServerParams();

    /**
     * Get cookie parameters
     * @return array
     */
    public function getCookieParams();

    /**
     * Get instance with cookies
     * @param array $cookies
     * @return static
     */
    public function withCookieParams(array $cookies);

    /**
     * Get query parameters
     * @return array
     */
    public function getQueryParams();

    /**
     * Get instance with query parameters
     * @param array $query
     * @return static
     */
    public function withQueryParams(array $query);

    /**
     * Get uploaded files
     * @return array
     */
    public function getUploadedFiles();

    /**
     * Get instance with uploaded files
     * @param array $uploadedFiles
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles);

    /**
     * Get deserialized body
     * @return null|array|object
     */
    public function getParsedBody();

    /**
     * Get instance with deserialized body
     * @param null|array|object
     * @return static
     */
    public function withParsedBody($data);

    /**
     * Get request attributes
     * @return mixed[]
     */
    public function getAttributes();

    /**
     * Get single attribute
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute($name, $default = null);

    /**
     * Get instance with added attribute
     * @param string $name
     * @param mixed $value
     * @return static
     */
    public function withAttribute($name, $value);

    /**
     * Get instance without attribute
     * @param string $name
     * @return static
     */
    public function withoutAttribute($name);

    /**
     * Get value from POST variable
     * @param string $name Variable name
     * @param mixed $default Default value
     * @return mixed Returns value or default
     */
    public function getPost($name, $default = null);

    /**
     * Get value from GET variable
     * @param string $name Variable name
     * @param mixed $default Default value
     * @return mixed Returns value or default
     */
    public function getQuery($name, $default = null);
}