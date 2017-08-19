<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 8/19/17
 * Time: 7:10 PM
 */

namespace Scalar\App;


use Scalar\Http\Message\RequestInterface;
use Scalar\Http\Message\ResponseInterface;
use Scalar\Router\AppInterface;

class App implements AppInterface
{

    /**
     * This function is being executed before the request is dispatched
     *
     * @param RequestInterface $request
     * @return RequestInterface
     */
    public function startup
    (
        $request
    )
    {
        // TODO: Implement startup() method.
    }

    /**
     * This function is being executed after the request has been dispatched
     * and the response is ready to be returned to the client
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function shutdown
    (
        $request,
        $response
    )
    {
        // TODO: Implement shutdown() method.
    }
}