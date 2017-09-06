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

namespace Scalar\Core\Router\Hook;


use Scalar\Core\Scalar;
use Scalar\Http\Message\ResponseInterface;
use Scalar\Http\Message\ServerRequestInterface;
use Scalar\Http\Middleware\HttpMiddlewareInterface;
use Scalar\Util\Annotation\PHPDoc;
use Scalar\Util\ScalarArray;

class ControllerDependencyInjectionHook implements HttpMiddlewareInterface
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
        if ($response && $response->hasCustomArgument('Controller') && class_exists($response->getCustomArgument('Controller'))) {
            $reflectionClass = new \ReflectionClass($response->getCustomArgument('Controller'));

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $phpDoc = new PHPDoc($reflectionProperty);
                $annotations = new ScalarArray($phpDoc->getAnnotations());

                if ($annotations->contains('Inject')) {
                    $service = Scalar::getService($annotations->get('Inject'));
                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue(null, $service);
                }
            }
        }
        return $next($request, $response);
    }
}