<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 07.06.17
 * Time: 10:06
 */

namespace Scaly\Router\Hook;

use Scaly\Http\Message\ResponseInterface;
use Scaly\Http\Message\ServerRequestInterface;
use Scaly\Http\Middleware\HttpMiddlewareInterface;

class MethodFilterMiddleware implements HttpMiddlewareInterface
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
    )
    {
        if ($response->hasCustomArgument("Method")) {
            $supportedMethods = $response->getCustomArgument("Method");
            if (!is_array($supportedMethods)) {
                $supportedMethods = [$supportedMethods];
            }
            if (!in_array($request->getMethod(), $supportedMethods)) {
                $response = $response->withStatus(405);
                return $response;
            }
        }
        return $next($request, $response);
    }
}