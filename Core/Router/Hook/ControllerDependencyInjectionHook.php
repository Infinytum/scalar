<?php

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

        if ($response->hasCustomArgument('Controller') && class_exists($response->getCustomArgument('Controller'))) {
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

        $response = $next($request, $response);

        return $response;
    }
}