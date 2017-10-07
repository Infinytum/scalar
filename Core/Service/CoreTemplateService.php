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
 * User: nila
 * Date: 10/6/17
 * Time: 3:46 PM
 */

namespace Scalar\Core\Service;


use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;
use Scalar\Core\Controller\AssetController;
use Scalar\Core\Router\Hook\TemplateHook;
use Scalar\Core\Scalar;
use Scalar\Core\Template\ViewBag;
use Scalar\Router\RouteEntry;
use Scalar\Util\ScalarArray;

class CoreTemplateService extends CoreService
{

    // Configuration

    const CONFIG_TEMPLATE_DIR = 'Location';
    const CONFIG_ASSETS_DIR = 'Assets';

    // Variables

    /**
     * CoreLogger instance
     * @var CoreLoggerService
     */
    private $coreLogger;

    /**
     * CoreLoader instance
     * @var CoreLoaderService
     */
    private $coreLoader;

    /**
     * CoreRouter instance
     * @var CoreRouterService
     */
    private $coreRouter;

    /**
     * Handlebars instance
     * @var Handlebars
     */
    private $handlebars;

    /**
     * Filesystem path where the templates reside
     * @var string
     */
    private $templateLocation;

    /**
     * Filesystem path where the assets reside
     * @var string
     */
    private $assetLocation;

    /**
     * CoreTemplateService constructor.
     */
    public function __construct()
    {
        $this->coreLogger = Scalar::getService(Scalar::SERVICE_CORE_LOGGER);
        $this->coreLoader = Scalar::getService(Scalar::SERVICE_CORE_LOADER);
        $this->coreRouter = Scalar::getService(Scalar::SERVICE_CORE_ROUTER);
        parent::__construct('TemplateEngine');
    }

    public function render
    (
        $template,
        $model = null
    )
    {
        if ($model === null) {
            $model = ViewBag::getArray();
        }

        return $this->handlebars->render($template, $model);
    }

    public function getHandlebars()
    {
        return $this->handlebars;
    }

    public function getAssetDirectory()
    {
        return $this->assetLocation;
    }

    public function getTemplateDirectory()
    {
        return $this->templateLocation;
    }

    /**
     * Initialize service for work
     *
     * @return bool
     */
    public function setup()
    {
        try {
            $this->addDefault(self::CONFIG_TEMPLATE_DIR, '{{App.Home}}/Resources/Templates/');
            $this->addDefault(self::CONFIG_ASSETS_DIR, '{{App.Home}}/Resources/Assets/');
        } catch (\Exception $exception) {

        }

        $this->templateLocation = $this->getValue(self::CONFIG_TEMPLATE_DIR);
        $this->assetLocation = $this->getValue(self::CONFIG_ASSETS_DIR);

        $this->coreLogger->v('Registering Handlebars namespace in core loader');
        $this->coreLoader->registerNamespace('\\Handlebars', SCALAR_CORE . '/Library/Handlebars/');

        $partialsLoader = new FilesystemLoader($this->templateLocation, ['extension' => 'scalar']);

        $this->handlebars = new Handlebars
        (
            [
                'loader' => $partialsLoader,
                'partials_loader' => $partialsLoader
            ]
        );

        $this->handlebars->addHelper('extends', function ($template, $context, $args, $source) {
            $viewBag = ViewBag::getArray();

            $viewBag['SubView'] = $this->handlebars->loadString($source)->render($viewBag);
            return $this->handlebars->render($args, $viewBag);
        });

        $assetRoute = new RouteEntry('/assets', null, true,
            new ScalarArray([
                'Controller' => AssetController::class,
                'Function' => 'assets'
            ])
        );

        $this->coreRouter->getRoutingTable()->addRoute($assetRoute);

        $this->coreLogger->d("Registered reverse asset proxy");

        $this->coreRouter->addMiddleware(new TemplateHook());

        $this->coreLogger->d("Registered template hook");

        return true;
    }

    /**
     * Destruct service and execute tearDown tasks
     *
     * @return bool
     */
    public function tearDown()
    {
        // TODO: Implement tearDown() method.
    }
}