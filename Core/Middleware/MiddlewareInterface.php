<?php

namespace Scaly\Core\Middleware;

interface MiddlewareInterface
{
    /**
     * Process object an then pass it to the next middleware
     *
     * @param object $response
     * @param MiddlewareInterface $next
     * @return object
     */
    public function process(
        $response,
        $next
    );
}