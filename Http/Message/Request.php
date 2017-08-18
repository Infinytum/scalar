<?php

namespace Scalar\Http\Message;


use InvalidArgumentException;
use Scalar\IO\Uri;
use Scalar\IO\UriInterface;

class Request extends Message implements RequestInterface
{

    /**
     * @var string
     */
    private $requestMethod;

    /**
     * @var Uri
     */
    private $uri;

    /**
     * @var string
     */
    private $requestTarget;

    public function __construct($method, $uri, $headers = [], $version = "1.0", $body = null)
    {
        parent::__construct($version, $headers, $body);
        $this->requestMethod = $method;
        $this->uri = $uri;
    }

    /**
     * Get request target
     * @return string
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget)
            return $this->requestTarget;
        $target = '/';
        if ($path = $this->uri->getPath()) {
            $target = $path;
        }
        if ($query = $this->uri->getQuery()) {
            $target = '?' . $query;
        }
        return $target;
    }

    /**
     * Get instance with request target
     * @param mixed $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget)
    {
        $newInstance = clone $this;
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException(
                'Target cannot contain any whitespaces!'
            );
        }
        $newInstance->requestTarget = $requestTarget;
        return $newInstance;
    }

    /**
     * Get request method
     * @return string
     */
    public function getMethod()
    {
        return $this->requestMethod;
    }

    /**
     * Get instance with request method
     * @param string $method
     * @return static
     */
    public function withMethod($method)
    {
        $newInstance = clone $this;
        $newInstance->requestMethod = $method;
        return $newInstance;
    }

    /**
     * Get request URI
     * @return UriInterface
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Get instance with URI
     * @param UriInterface $uri
     * @param bool $preserveHost
     * @return static
     */
    public function withUri($uri, $preserveHost = false)
    {
        $newInstance = clone $this;
        $newInstance->uri = $uri;

        if (!$preserveHost) {
            $host = $newInstance->uri->getHost();
            if ($port = $newInstance->uri->getPort())
                $host .= ':' . $port;
            if ($newInstance->hasHeader("Host"))
                $newInstance = $newInstance->withHeader("Host", $host);
        }
        return $newInstance;
    }
}