<?php
/**
 * @autor     Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2014 Tom Forrer (http://github.com/tmf)
 */

namespace Tmf\Wordpress\Container;

/**
 * Class HookableService
 *
 * @package Tmf\Container
 */
abstract class HookableService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * By default the HookableServiceProvider calls the "initialize" method when the 'init' WordPress action is
     * triggered. Override this method in your service, or define your own entry point when registering a
     * HookableServiceProvider.
     */
    public function initialize()
    {

    }
}