<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 06.06.17
 * Time: 19:04
 */

namespace Scalar\Http\Factory;


use Scalar\Http\Message\Response;
use Scalar\Http\Message\ResponseInterface;
use Scalar\IO\Factory\StreamFactory;

class ResponseFactory implements ResponseFactoryInterface
{

    /**
     * Create a new response.
     *
     * @param int $code HTTP status code
     * @return ResponseInterface
     */
    public function createResponse($code = 200)
    {
        $streamFactory = new StreamFactory();
        return new Response
        (
            $streamFactory->createStream(''),
            [],
            [],
            "1.0",
            $code
        );
    }
}