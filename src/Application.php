<?php

namespace Interop\Framework;

use Acclimate\Container\CompositeContainer;
use Exception;
use Interop\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Zend\Stratigility\MiddlewarePipe;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


class Application extends MiddlewarePipe
{
    /**
     * @var CompositeContainer
     */
    protected $container;

    /**
     * @var Module[]
     */
    protected $modules;

    /**
     *
     * @param  ModuleInterface[]|array              $modules   An array of strings (the class name of the module), or objects implementing ModuleInterface
     * @param  ContainerInterface $container
     * @throws Exception
     */
    public function __construct(array $modules, ContainerInterface $container = null)
    {
        $this->container = new CompositeContainer();

        if ($container) {
            $this->container->addContainer($container);
        }

        // Instantiate every module
        foreach ($modules as $class) {
            if ($class instanceof ModuleInterface) {
                $module = $class;
                $this->modules[get_class($module)] = $module;
            } else {
                if (! is_subclass_of($class, ModuleInterface::class)) {
                    throw new Exception("$class is not an instance of ".ModuleInterface::class);
                }

                /** @var ModuleInterface $module */
                $module = new $class();

                $this->modules[$class] = $module;
            }

            // Register the module's container
            $subContainer = $module->getContainer($this->container);
            if ($subContainer) {
                $this->container->addContainer($subContainer);
            }

            // Register the module's HTTP routers
            foreach ($this->modules as $module) {
                if ($module instanceof HttpModuleInterface) {
                    // Note: we could use a "getPath" here as first argument
                    $this->pipe($router->getMiddleware());
                }
            }
        }
    }

    protected function init()
    {
        // Init every module
        foreach ($this->modules as $module) {
            $module->init();
        }
    }


    /**
     * Handle a request
     *
     * Takes the pipeline, creates a Next handler, and delegates to the
     * Next handler.
     *
     * If $out is a callable, it is used as the "final handler" when
     * $next has exhausted the pipeline; otherwise, a FinalHandler instance
     * is created and passed to $next during initialization.
     *
     * @param Request $request
     * @param Response $response
     * @param callable $out
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        $this->init();
        return parent::__invoke($request, $response, $out);
    }
}
