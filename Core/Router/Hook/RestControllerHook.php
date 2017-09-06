<?php
/**
 * (C) 2017 by Michael Teuscher (mk.teuscher@gmail.com)
 * as part of the Scalar PHP framework
 *
 * Released under the AGPL v3.0 license
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 7/18/17
 * Time: 10:37 AM
 */

namespace Scalar\Core\Router\Hook;


use Scalar\Core\Router\Controller\RestController;
use Scalar\Http\Message\ResponseInterface;
use Scalar\Http\Message\ServerRequestInterface;
use Scalar\Http\Middleware\HttpMiddlewareInterface;

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
        if ($response && $response->hasCustomArgument(self::ARG_CONTROLLER)) {
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
        return $next($request, $response);
    }
}