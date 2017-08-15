<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 7/18/17
 * Time: 10:37 AM
 */

namespace Scaly\Router\Hook;


use Scaly\Http\Message\ResponseInterface;
use Scaly\Http\Message\ServerRequestInterface;
use Scaly\Http\Middleware\HttpMiddlewareInterface;
use Scaly\Router\Controller\RestController;

class RestControllerHook implements HttpMiddlewareInterface
{

    const ARG_CONTROLLER = 'Controller';

    const FUNC_GET = 'get';
    const FUNC_POST = 'create';
    const FUNC_PUT = 'update';
    const FUNC_PATCH = 'patch';
    const FUNC_DELETE = 'delete';

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

        if ($response->hasCustomArgument(self::ARG_CONTROLLER)) {
            $interfaces = class_implements($response->getCustomArgument(self::ARG_CONTROLLER));

            if (is_array($interfaces) && in_array(RestController::class, $interfaces)) {
                switch ($request->getMethod()) {
                    case 'GET':
                        $response = $response->withAddedCustomArgument('Function', self::FUNC_GET);
                        break;
                    case 'POST':
                        $response = $response->withAddedCustomArgument('Function', self::FUNC_POST);
                        break;
                    case 'PUT':
                        $response = $response->withAddedCustomArgument('Function', self::FUNC_PUT);
                        break;
                    case 'PATCH':
                        $response = $response->withAddedCustomArgument('Function', self::FUNC_PATCH);
                        break;
                    case 'DELETE':
                        $response = $response->withAddedCustomArgument('Function', self::FUNC_DELETE);
                        break;
                }

            }

        }

        $response = $next($request, $response);

        return $response;
    }
}