<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 7/11/17
 * Time: 11:26 AM
 */

namespace Scalar\Template\Hook;

use Scalar\Http\Message\ResponseInterface;
use Scalar\Http\Message\ServerRequestInterface;
use Scalar\Http\Middleware\HttpMiddlewareInterface;
use Scalar\Template\Templater;
use Scalar\Template\ViewBag;

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
        $template = null;
        if ($response->hasCustomArgument("Template")) {
            $templateName = $response->getCustomArgument("Template");
            $template = Templater::getInstance()->buildFullTemplate($templateName);
        }
        if ($template) {
            $renderEngine = new \Mustache_Engine;
            $renderedTemplate = $renderEngine->render($template->getRawTemplate(), ViewBag::getArray());
            $response->getBody()->write($renderedTemplate);
        }


        return $response;
    }
}