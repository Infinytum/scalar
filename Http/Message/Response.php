<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 05.06.17
 * Time: 00:08
 */

namespace Scaly\Http\Message;


use Scaly\IO\Stream\StreamInterface;

class Response implements ResponseInterface
{


    protected $messages = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );
    /**
     * @var int
     */
    private $statusCode;
    /**
     * @var string
     */
    private $reasonMessage;
    /**
     * @var string
     */
    private $protocolVersion;
    /**
     * @var array
     */
    private $headers;
    /**
     * @var StreamInterface
     */
    private $body;

    /**
     * @var array
     */
    private $customArguments;

    function __construct
    (
        $body,
        $headers = [],
        $customArguments = [],
        $protocolVersion = '1.0',
        $statusCode = 200,
        $reasonMessage = null
    )
    {
        if (!$body instanceof StreamInterface) {
            throw new \InvalidArgumentException(
                'Body was not an instance of StreamInterface'
            );
        }

        $this->body = $body;
        $this->headers = $headers;
        $this->customArguments = $customArguments;
        $this->protocolVersion = $protocolVersion;
        $this->reasonMessage = $this->withStatus($statusCode, $reasonMessage)->reasonMessage;
        $this->statusCode = $statusCode;
    }

    /**
     * Get instance with status code
     * @param int $code
     * @param string $reason
     * @return static
     */
    public function withStatus($code, $reason = null)
    {
        if (!$reason) {
            if (array_key_exists($code, $this->messages)) {
                $reason = $this->messages[$code];
            } else {
                $reason = 'Unknown Reason';
            }
        }

        $newInstance = clone $this;

        $newInstance->statusCode = $code;
        $newInstance->reasonMessage = $reason;

        return $newInstance;
    }

    /**
     * Get protocol version
     * @return string protocol version.
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * Get instance with protocol version
     * @param string $version protocol version
     * @return static
     */
    public function withProtocolVersion($version)
    {
        $newInstance = clone $this;
        $newInstance->protocolVersion = $version;
        return $newInstance;
    }

    /**
     * Get header value as concatenated string
     * @param string $name header name.
     * @return string Concatenated header value
     */
    public function getHeaderLine($name)
    {
        if (!$this->hasHeader($name)) {
            return $name . ':';
        }

        return $name . ': ' . join(', ', $this->getHeader($name));
    }

    /**
     * Check if header is present
     * @param string $name header name.
     * @return bool
     */
    public function hasHeader($name)
    {
        return array_key_exists($name, $this->getHeaders());
    }

    /**
     * Get all headers
     * @return string[][] Returns an associative array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get header values as array
     * @param string $name header name.
     * @return string[] An array of string values
     */
    public function getHeader($name)
    {
        if (!$this->hasHeader($name)) {
            return [''];
        }

        return $this->headers[$name];
    }

    /**
     * Get instance with header
     * @param string $name header name.
     * @param string|string[] $value Header value.
     * @return static
     */
    public function withHeader($name, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        $newInstance = clone $this;
        $newInstance->headers = array($name => $value);
        return $newInstance;
    }

    /**
     * Get message with added header
     * @param string $name header to add.
     * @param string|string[] $value Header value.
     * @return static
     */
    public function withAddedHeader($name, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        $newInstance = clone $this;
        $newInstance->headers[$name] = $value;
        return $newInstance;
    }

    /**
     * Get message without header
     * @param string $name header to remove.
     * @return static
     */
    public function withoutHeader($name)
    {
        if (!$this->hasHeader($name))
            return $this;

        $newInstance = clone $this;
        unset($newInstance->headers[$name]);
        return $newInstance;
    }

    /**
     * Get custom argument value
     * @param string $name argument name.
     * @return mixed Value
     */
    public function getCustomArgument($name)
    {
        if (!$this->hasCustomArgument($name)) {
            return null;
        }
        return $this->customArguments[$name];
    }

    /**
     * Check if argument is present
     * @param string $name argument name.
     * @return bool
     */
    public function hasCustomArgument($name)
    {
        return array_key_exists($name, $this->getCustomArguments());
    }

    /**
     * Get all arguments
     * @return mixed
     */
    public function getCustomArguments()
    {
        return $this->customArguments;
    }

    /**
     * Get instance with argument
     * @param string $name argument name.
     * @param mixed $value Anything
     * @return static
     */
    public function withCustomArgument($name, $value)
    {
        $newInstance = clone $this;
        $newInstance->customArguments = array($name => $value);
        return $newInstance;
    }

    /**
     * Get message with added argument
     * @param string $name argument name.
     * @param mixed $value Anything
     * @return static
     */
    public function withAddedCustomArgument($name, $value)
    {
        $newInstance = clone $this;
        $newInstance->customArguments[$name] = $value;
        return $newInstance;
    }

    /**
     * Get message without argument
     * @param string $name argument to remove.
     * @return static
     */
    public function withoutCustomArgument($name)
    {
        if (!$this->hasCustomArgument($name))
            return $this;

        $newInstance = clone $this;
        unset($newInstance->customArguments[$name]);
        return $newInstance;
    }

    /**
     * Get body of message
     *
     * @return StreamInterface Message body
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get message with body
     * @param StreamInterface $body Body.
     * @return static
     */
    public function withBody($body)
    {
        if (!$body instanceof StreamInterface) {
            throw new \InvalidArgumentException(
                'Body was not an instance of StreamInterface'
            );
        }
        $newInstance = clone $this;
        $newInstance->body = $body;
        return $newInstance;
    }

    /**
     * Get response code
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get response reason
     * @return string Reason
     */
    public function getReasonPhrase()
    {
        return $this->reasonMessage;
    }
}