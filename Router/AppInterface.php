<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 7/14/17
 * Time: 10:09 AM
 */

namespace Scalar\Router;


use Scalar\Http\Message\RequestInterface;
use Scalar\Http\Message\ResponseInterface;

interface AppInterface
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
    );


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
    );
}