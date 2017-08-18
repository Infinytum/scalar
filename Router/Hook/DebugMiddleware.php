<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 09.06.17
 * Time: 21:48
 */

namespace Scalar\Router\Hook;


use Scalar\Core\Scalar;
use Scalar\Http\Message\ResponseInterface;
use Scalar\Http\Message\ServerRequestInterface;
use Scalar\Http\Middleware\HttpMiddlewareInterface;

class DebugMiddleware implements HttpMiddlewareInterface
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

        /**
         * @var $response ResponseInterface
         */
        $response = $next($request, $response);


        $response->getBody()->write(PHP_EOL);
        $response->getBody()->write(PHP_EOL);
        $response->getBody()->write(PHP_EOL);
        $response->getBody()->write('// SCALAR DEBUG');
        $response->getBody()->write(PHP_EOL);
        $response->getBody()->write(PHP_EOL);
        foreach (Scalar::getLogger()->getLogArray() as $logLine) {
            $response->getBody()->write($logLine . PHP_EOL);
        }

        return $response;
    }
}