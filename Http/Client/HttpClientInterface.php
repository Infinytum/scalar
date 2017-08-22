<?php

namespace Scalar\Http\Client;

use Scalar\Http\Message\ResponseInterface;
use Scalar\Http\Message\ServerRequestInterface;

interface HttpClientInterface
{

    /**
     * Set request to execute
     *
     * @param $serverRequest ServerRequestInterface
     * @return void
     */
    public function setRequest
    (
        $serverRequest
    );


    /**
     * Get request
     *
     * @return ServerRequestInterface
     */
    public function getRequest();

    /**
     * Execute request to remote
     *
     * @return mixed
     */
    public function request();


    /**
     * Get response after execution
     *
     * @return ResponseInterface
     */
    public function getResponse();

    /**
     * Check if implementation is currently available
     *
     * @return bool
     */
    public function isAvailable();

}