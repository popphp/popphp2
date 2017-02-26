<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Module;

use Pop\Application;

/**
 * Pop module class
 *
 * @category   Pop
 * @package    Pop\Module
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Module implements ModuleInterface, \ArrayAccess
{

    /**
     * Module config
     * @var mixed
     */
    protected $config = null;

    /**
     * Application
     * @var Application
     */
    protected $application = null;

    /**
     * Module name
     * @var string
     */
    protected $name = null;

    /**
     * Constructor
     *
     * Instantiate a module object
     *
     * Optional parameters are an application instance or a configuration object or array
     */
    public function __construct()
    {
        $args        = func_get_args();
        $application = null;
        $config      = null;
        $name        = null;

        foreach ($args as $arg) {
            if ($arg instanceof Application) {
                $application = $arg;
            } else if (is_array($arg) || ($arg instanceof \ArrayAccess) || ($arg instanceof \ArrayObject)) {
                $config = $arg;
            } else if (is_string($arg)) {
                $name = $arg;
            }
        }

        if (null !== $name) {
            $this->setName($name);
        }

        if (null !== $config) {
            $this->registerConfig($config);
        }

        if (null !== $application) {
            $this->register($application);
        }
    }

    /**
     * Set module name
     *
     * @param  string $name
     * @return Module
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get module name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Determine if module has name
     *
     * @return boolean
     */
    public function hasName()
    {
        return (null !== $this->name);
    }

    /**
     * Register a configuration with the module object
     *
     * @param  mixed $config
     * @throws \InvalidArgumentException
     * @return Module
     */
    public function registerConfig($config)
    {
        if (!is_array($config) && !($config instanceof \ArrayAccess) && !($config instanceof \ArrayObject)) {
            throw new \InvalidArgumentException(
                'Error: The config must be either an array itself or implement ArrayAccess or extend ArrayObject.'
            );
        }

        $this->config = $config;

        return $this;
    }

    /**
     * Register module
     *
     * @param  Application $application
     * @return Module
     */
    public function register(Application $application)
    {
        $this->application = $application;

        if (null !== $this->config) {
            // If the autoloader is set and the the module config has a
            // defined prefix and src, register the module with the autoloader
            if ((null !== $this->application) && (null !== $this->application->autoloader()) &&
                isset($this->config['prefix']) && isset($this->config['src']) && file_exists($this->config['src'])
            ) {
                // Register as PSR-0
                if (isset($this->config['psr-0']) && ($this->config['psr-0'])) {
                    $this->application->autoloader()->add($this->config['prefix'], $this->config['src']);
                // Else, default to PSR-4
                } else {
                    $this->application->autoloader()->addPsr4($this->config['prefix'], $this->config['src']);
                }
            }

            // If routes are set in the module config, register them with the application
            if (isset($this->config['routes']) && (null !== $this->application) && (null !== $this->application->router())) {
                $this->application->router()->addRoutes($this->config['routes']);
            }

            // If services are set in the module config, register them with the application
            if (isset($this->config['services']) && (null !== $this->application) && (null !== $this->application->services())) {
                foreach ($this->config['services'] as $name => $service) {
                    $this->application->setService($name, $service);
                }
            }

            // If events are set in the app config, register them with the application
            if (isset($this->config['events']) && (null !== $this->application) && (null !== $this->application->events())) {
                foreach ($this->config['events'] as $event) {
                    if (isset($event['name']) && isset($event['action'])) {
                        $this->application->on(
                            $event['name'],
                            $event['action'],
                            ((isset($event['priority'])) ? $event['priority'] : 0)
                        );
                    }
                }
            }
        }

        $this->application->modules->register($this);

        return $this;
    }

    /**
     * Merge new or altered config values with the existing config values
     *
     * @param  mixed   $config
     * @param  boolean $preserve
     * @return Module
     */
    public function mergeConfig($config, $preserve = false)
    {
        if ($this->config instanceof \Pop\Config\Config) {
            $this->config->merge($config, $preserve);
        } else if (is_array($config) || ($config instanceof \ArrayAccess) || ($config instanceof \ArrayObject)) {
            if (null !== $this->config) {
                $this->config = ($preserve) ? array_merge_recursive($this->config, $config) :
                    array_replace_recursive($this->config, $config);
            } else {
                $this->config = $config;
            }
        }

        return $this;
    }

    /**
     * Get application
     *
     * @return Application
     */
    public function application()
    {
        return $this->application;
    }

    /**
     * Access module config
     *
     * @return mixed
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * Determine if the module has been registered with an application object
     *
     * @return boolean
     */
    public function isRegistered()
    {
        return ((null !== $this->application) &&
            (null !== $this->application->modules()) && ($this->application->modules()->hasModule($this)));
    }

    /**
     * Set a pre-designated value in the module object
     *
     * @param  string $name
     * @param  mixed  $value
     * @return Module
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'config':
                $this->registerConfig($value);
                break;

        }
        return $this;
    }

    /**
     * Get a pre-designated value from the module object
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'config':
                return $this->config;
                break;
            default:
                return null;
        }
    }

    /**
     * Determine if a pre-designated value in the module object exists
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        switch ($name) {
            case 'config':
                return (null !== $this->config);
                break;
            default:
                return false;
        }
    }

    /**
     * Unset a pre-designated value in the module object
     *
     * @param  string $name
     * @return Module
     */
    public function __unset($name)
    {
        switch ($name) {
            case 'config':
                $this->config = null;
                break;
        }

        return $this;
    }

    /**
     * Set a value in the array
     *
     * @param  string $offset
     * @param  mixed  $value
     * @return Module
     */
    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    /**
     * Get a value from the array
     *
     * @param  string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * Determine if a value exists
     *
     * @param  string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * Unset a value from the array
     *
     * @param  string $offset
     * @return Module
     */
    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }

}