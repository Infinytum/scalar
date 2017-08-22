<?php

namespace Scalar\Template\Hook;

use Scalar\Core\Scalar;
use Scalar\Http\Message\ResponseInterface;
use Scalar\Http\Message\ServerRequestInterface;
use Scalar\Http\Middleware\HttpMiddlewareInterface;

class TemplateHook implements HttpMiddlewareInterface
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
        if ($response->hasCustomArgument("Template")) {
            $templateName = $response->getCustomArgument("Template");
            $templateEngine = Scalar::getService
            (
                Scalar::SERVICE_TEMPLATER
            );

            $template = $templateEngine->buildFullTemplate($templateName);

            $response
                ->getBody()
                ->write($templateEngine->renderTemplate($template));
        }
        return $response;
    }
}