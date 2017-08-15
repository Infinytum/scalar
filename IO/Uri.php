<?php

namespace Scaly\IO;


use Scaly\IO\Exception\MalformedUriException;

class Uri implements UriInterface
{

    private static $uriRegex = '/^
(?<Scheme>[a-zA-Z]+)
(?::\/\/)
(?<UserInfo>
    (?<User>[^:]+?)
    (?::){0,1}
    (?<Pass>[^:]+?)
    (?:@){1}
)?
(?<Host>[^:\/]+)
(?::){0,1}
(?<Port>[0-9]+)?
(?<Path>[^?#\s]+)?
(?:\?){0,1}
(?<Query>[^#\s]+)?
(?:\#){0,1}
(?<Fragment>.+?)?
$/xm';

    private $scheme;
    private $username;
    private $password;
    private $host;
    private $port;
    private $path;
    private $query;
    private $fragment;

    /**
     * Uri constructor.
     * @param $scheme
     * @param $username
     * @param $password
     * @param $host
     * @param $port
     * @param $path
     * @param $query
     * @param $fragment
     */
    public function __construct($scheme = null, $username = null, $password = null, $host = null, $port = null, $path = null, $query = null, $fragment = null)
    {
        $this->scheme = $scheme;
        $this->username = $username;
        $this->password = $password;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
    }

    /**
     * Get URI instance with host
     * @param string $host The hostname
     * @return static A new instance with the specified host.
     * @throws \InvalidArgumentException for invalid schemes.
     */
    public function withHost($host)
    {
        $uri = clone $this;
        $uri->host = $host;
        return $uri;
    }

    /**
     * Get URI instance with port
     * @param null|int $port The port
     * @return static A new instance with the specified port.
     * @throws \InvalidArgumentException for invalid schemes.
     */
    public function withPort($port)
    {
        $uri = clone $this;
        $uri->port = $port;
        return $uri;
    }

    /**
     * Get URI instance with path
     * @param string $path The path
     * @return static A new instance with the specified path.
     * @throws \InvalidArgumentException for invalid schemes.
     */
    public function withPath($path)
    {
        $uri = clone $this;
        $uri->path = $path;
        return $uri;
    }

    /**
     * Get URI instance with scheme
     * @param string $scheme The scheme
     * @return static A new instance with the specified scheme.
     * @throws \InvalidArgumentException for invalid schemes.
     */
    public function withScheme($scheme)
    {
        $uri = clone $this;
        $uri->scheme = $scheme;
        return $uri;
    }

    /**
     * Get URI instance with user info
     *
     * @param string $user The username
     * @param null|string $password The password
     * @return static A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null)
    {
        $uri = clone $this;
        $uri->username = $user;
        $uri->password = $password;
        return $uri;
    }

    /**
     * Get URI instance with query
     * @param string $query The query
     * @return static A new instance with the specified query string.
     * @throws \InvalidArgumentException for invalid schemes.
     */
    public function withQuery($query)
    {
        $uri = clone $this;
        $uri->query = $query;
        return $uri;
    }

    /**
     * Get URI with fragment
     * @param string $fragment The fragment
     * @return static A new instance with the specified fragment.
     */
    public function withFragment($fragment)
    {
        $uri = clone $this;
        $uri->fragment = $fragment;
        return $uri;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return $this->__toString();
    }

    /**
     * Return URI as string
     *
     * @return string
     */
    public function __toString()
    {

        $query = $this->getQuery() ? '?' . $this->getQuery() : '';
        $fragment = $this->getFragment() ? '#' . $this->getFragment() : '';

        return
            $this->getScheme()
            . '://'
            . $this->getAuthority()
            . $this->getPath()
            . $query
            . $fragment;
    }

    /**
     * Get URI query string
     * @return string The URI query string.
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get URI fragment
     * @return string The URI fragment.
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Get URI Scheme
     * @return string The URI scheme.
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Get URI authority
     * @return string The URI authority
     */
    public function getAuthority()
    {
        $userInfo = $this->getUserInfo() ? $this->getUserInfo() . '@' : '';
        return $userInfo . $this->getHost() . ($this->getPort() ? (':' . $this->getPort()) : '');
    }

    /**
     * Get URI user information
     * @return string The URI user information
     */
    public function getUserInfo()
    {
        return $this->username . ($this->password ? (':' . $this->password) : '');
    }

    /**
     * Get URI host
     * @return string The URI host.
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get URI port
     * @return int|null The URI port.
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get URI path
     * @return string The URI path.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $uri = $this->fromString($serialized);
        $this->scheme = $uri->scheme;
        $this->username = $uri->username;
        $this->password = $uri->password;
        $this->host = $uri->host;
        $this->port = $uri->port;
        $this->path = $uri->path;
        $this->query = $uri->query;
        $this->fragment = $uri->fragment;
    }

    /**
     * Get URI instance from string
     * @param string $uri URI as string
     * @return static A new instance with parameters parsed from the string
     * @throws MalformedUriException
     */
    public function fromString($uri)
    {
        if (!preg_match(self::$uriRegex, $uri, $parsedUri)) {
            throw new MalformedUriException($uri);
        }

        return new Uri(
            parse_url($uri, PHP_URL_SCHEME),
            parse_url($uri, PHP_URL_USER),
            parse_url($uri, PHP_URL_PASS),
            parse_url($uri, PHP_URL_HOST),
            parse_url($uri, PHP_URL_PORT),
            parse_url($uri, PHP_URL_PATH),
            parse_url($uri, PHP_URL_QUERY),
            parse_url($uri, PHP_URL_FRAGMENT)
        );
    }
}