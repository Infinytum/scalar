<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 09.06.17
 * Time: 21:48
 */

namespace Scaly\Router\Hook;


use Scaly\Core\Scaly;
use Scaly\Http\Message\ResponseInterface;
use Scaly\Http\Message\ServerRequestInterface;
use Scaly\Http\Middleware\HttpMiddlewareInterface;

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
        $response->getBody()->write('// SCALY DEBUG');
        $response->getBody()->write(PHP_EOL);
        $response->getBody()->write(PHP_EOL);
        foreach (Scaly::getLogger()->getLogArray() as $logLine) {
            $response->getBody()->write($logLine . PHP_EOL);
        }

        return $response;
    }
}