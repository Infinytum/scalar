<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 06.06.17
 * Time: 19:04
 */

namespace Scaly\Http\Factory;


use Scaly\Http\Message\Response;
use Scaly\Http\Message\ResponseInterface;
use Scaly\IO\Factory\StreamFactory;

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