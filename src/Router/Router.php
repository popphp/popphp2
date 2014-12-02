<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp2
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Router;

/**
 * Pop router class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Router implements RouterInterface
{

    /**
     * Array of available routes
     * @var array
     */
    protected $routes = [];

    /**
     * Array of route parameters
     * @var array
     */
    protected $routeParams = [];

    /**
     * Array of dispatch parameters
     * @var array
     */
    protected $dispatchParams = [];

    /**
     * Route match object
     * @var Match\MatchInterface
     */
    protected $routeMatch = null;

    /**
     * Controller class name
     * @var string
     */
    protected $controllerClass = null;

    /**
     * Controller object
     * @var \Pop\Controller\ControllerInterface
     */
    protected $controller = null;

    /**
     * Constructor
     *
     * Instantiate the router object
     *
     * @param  array $routes
     * @return Router
     */
    public function __construct(array $routes = null)
    {
        $this->routeMatch = (stripos(php_sapi_name(), 'cli') !== false) ? new Match\Cli() : new Match\Http();
        if (null !== $routes) {
            $this->addRoutes($routes);
        }
    }

    /**
     * Add a route
     *
     * @param  string $route
     * @param  string $controller
     * @return Router
     */
    public function addRoute($route, $controller)
    {
        if (!isset($this->routes[$route])) {
            $this->routes[$route] = $controller;
        } else {
            if (is_array($this->routes[$route]) && is_array($controller)) {
                $this->routes[$route] = array_merge_recursive($this->routes[$route], $controller);
            } else {
                $this->routes[$route] = $controller;
            }
        }

        return $this;
    }

    /**
     * Add multiple controller routes
     *
     * @param  array $routes
     * @return Router
     */
    public function addRoutes(array $routes)
    {
        foreach ($routes as $route => $controller) {
            $this->addRoute($route, $controller);
        }

        return $this;
    }

    /**
     * Add route params to be passed into a new controller instance
     *
     *     $router->addRouteParams('MyApp\Controller\IndexController', ['foo', 'bar']);
     *
     * @param  string $route
     * @param  mixed  $params
     * @return Router
     */
    public function addRouteParams($route, $params)
    {
        $this->routeParams[$route] = $params;
        return $this;
    }

    /**
     * Add dispatch params to be passed into the dispatched method of the controller instance
     *
     *     $router->addDispatchParams('MyApp\Controller\IndexController->foo', ['bar', 'baz']);
     *
     * @param  string $dispatch
     * @param  mixed  $params
     * @return Router
     */
    public function addDispatchParams($dispatch, $params)
    {
        $this->dispatchParams[$dispatch] = $params;
        return $this;
    }

    /**
     * Get the params assigned to the route
     *
     * @param  string $route
     * @return mixed
     */
    public function getRouterParams($route)
    {
        return (isset($this->routeParams[$route])) ? $this->routeParams[$route] : null;
    }

    /**
     * Get the params assigned to the dispatch
     *
     * @param  string $dispatch
     * @return mixed
     */
    public function getDispatchParams($dispatch)
    {
        return (isset($this->dispatchParams[$dispatch])) ? $this->dispatchParams[$dispatch] : null;
    }

    /**
     * Get route
     *
     * @return mixed
     */
    public function getRoute()
    {
        return $this->routeMatch->match($this->routes);
    }

    /**
     * Determine if a route is set for the current request
     *
     * @return boolean
     */
    public function hasRoute()
    {
        return (null !== $this->getRoute());
    }

    /**
     * Get routes
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Get route match object
     *
     * @return Match\MatchInterface
     */
    public function getRouteMatch()
    {
        return $this->routeMatch;
    }

    /**
     * Get the current controller object
     *
     * @return \Pop\Controller\ControllerInterface
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get the current controller class name
     *
     * @return string
     */
    public function getControllerClass()
    {
        return $this->controllerClass;
    }

    /**
     * Route to the correct controller
     *
     * @return void
     */
    public function route()
    {
        $controllerClass       = $this->getRoute();
        $this->controllerClass = $controllerClass;

        if ((null !== $controllerClass) && class_exists($controllerClass)) {
            // If the controller has route parameters
            if (isset($this->routeParams[$controllerClass])) {
                $params = $this->routeParams[$controllerClass];
                if (!is_array($params)) {
                    $params = [$params];
                }
                $reflect          = new \ReflectionClass($controllerClass);
                $this->controller = $reflect->newInstanceArgs($params);
            // Else, just instantiate the controller
            } else {
                $this->controller = new $controllerClass();
            }
        }
    }

}