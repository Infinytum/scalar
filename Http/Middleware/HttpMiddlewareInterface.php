<?php

namespace Scaly\Http\Middleware;

use Scaly\Http\Message\ResponseInterface;
use Scaly\Http\Message\ServerRequestInterface;

interface HttpMiddlewareInterface
{
    /**
     * Process object an then pass it to the next middleware
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param \Closure $next
     * @return object
     */
    public function process(
        $request,
        $response,
        $next
    );
}