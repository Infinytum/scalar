<?php

namespace Scalar\Http\Factory;


use Scalar\Http\Message\ResponseInterface;

interface ResponseFactoryInterface
{

    /**
     * Create a new response.
     *
     * @param int $code HTTP status code
     * @return ResponseInterface
     */
    public function createResponse($code = 200);

}