<?php
/**
 * @autor     Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2014 Tom Forrer (http://github.com/tmf)
 */

namespace Tmf\Wordpress\Container;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class HookableServiceProvider
 *
 * @package Tmf\Container
 */
class HookableServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string the key for the service in the pimple service container
     */
    protected $serviceKey;
    /**
     * @var string the fully qualified class name for the service factory method
     */
    protected $serviceClass;
    /**
     * @var array an array of (public) service methods names indexed by hook name. i.e. array('init' => 'initialize')
     */
    protected $hookConnections = array();

    /**
     * Construct a HookableServiceProvider with all needed parameters.
     * By default, the entry point (the point where the service is at least instantiated by the container)
     * during the request lifecycle is the 'init' action.
     *
     * @param string $serviceKey              a key for the container
     * @param string $serviceClass            the fully qualified class name of the service (should extend the
     *                                        HookableService class, but can be any class)
     * @param array  $hookConnections         a list of entry points: each value containing a method name of the
     *                                        service will get called, when the wordpress filter/action specified in
     *                                        the key are triggered.
     *                                        By default, 'initialize' is called when 'init' is triggered
     */
    public function __construct($serviceKey, $serviceClass, $hookConnections = [['hook' => 'init', 'method' => 'initialize', 'priority' => 10]])
    {
        $this->serviceKey = $serviceKey;
        $this->serviceClass = $serviceClass;

        foreach($hookConnections as $index => $hookConnection){
            if (!isset($hookConnections[$index]['priority'])) {
                $hookConnections[$index]['priority'] = 10;
            }
        }

        $this->hookConnections = $hookConnections;
    }

    /**
     * Register a HookableService (or any kind of class) as a service, where when the wordpress hook callback is called,
     * the service gets instantiated and gets called with a specified initalization method.
     *
     * @param Container $container
     */
    public function register(Container $container)
    {
        $serviceKey = $this->serviceKey;
        $serviceClassKey = $this->serviceKey . '.class';

        // set the service class parameter in the container
        $container[$serviceClassKey] = $this->serviceClass;

        // set service instantiation function
        $container[$serviceKey] = function ($container) use ($serviceClassKey) {
            // instantiate the class from the class parameter
            $service = new $container[$serviceClassKey]();

            // inject the container, if supported
            if ($service instanceof ContainerAwareInterface) {
                $service->setContainer($container);
            }

            return $service;
        };

        // add actions and filters to request, exactly when needed, the service from the container
        foreach ($this->hookConnections as $hookConnection) {
            $this->hookService($hookConnection['hook'], $hookConnection['method'], $hookConnection['priority'], $container, $serviceKey);
        }
    }

    /**
     * @param string $hook      the WordPress hook, an action or a filter
     * @param string $method    the method to hook up
     * @param int    $priority  the action's or filter's priority
     * @param object $container the Pimple container
     * @param string $serviceKey
     */
    protected function hookService($hook, $method, $priority, $container, $serviceKey)
    {

        // use an anonymous function to "use" the $container $serviceKey and $method objects/values
        add_filter($hook, function () use ($container, $serviceKey, $method) {

            // call the specified method with all arguments provided by the action / filter, only when present
            if (method_exists($container[$serviceKey], $method)) {

                // if the $param_arr array has more elements than the method has arguments: it works fine. more method arguments than elements in $param_arr triggers an error
                call_user_func_array(array($container[$serviceKey], $method), func_get_args());
            }
        }, $priority, 100); // 100 should be enough accepted arguments. call_user_func_array will take care of the rest
    }
} 