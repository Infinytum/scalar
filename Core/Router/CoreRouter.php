<?php

namespace Scalar\Core\Router;

use Scalar\Core\Router\Hook\ControllerDependencyInjectionHook;
use Scalar\Core\Router\Hook\MethodFilterMiddleware;
use Scalar\Core\Router\Hook\RestControllerHook;
use Scalar\Core\Scalar;
use Scalar\Router\Router;
use Scalar\Template\Hook\TemplateHook;

class CoreRouter extends Router
{

    public function __construct()
    {
        $scalarConfig = Scalar::getService(Scalar::SERVICE_SCALAR_CONFIG);
        parent::__construct
        (
            $scalarConfig->get(Scalar::CONFIG_ROUTER_MAP),
            $scalarConfig->get(Scalar::CONFIG_ROUTER_CONTROLLER)
        );

        $this->addHandler(new MethodFilterMiddleware());
        $this->addHandler(new TemplateHook());
        $this->addHandler(new RestControllerHook());
        $this->addHandler(new ControllerDependencyInjectionHook());

        if (Scalar::isDeveloperMode()) {
            $this->generateRouteMap();
        }
    }

}