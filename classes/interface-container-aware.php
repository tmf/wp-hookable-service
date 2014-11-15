<?php
/**
 * @autor     Tom Forrer <tom.forrer@gmail.com>
 * @copyright Copyright (c) 2014 Tom Forrer (http://github.com/tmf)
 */

namespace Tmf\Wordpress\Container;

use Pimple\Container;

/**
 * Interface ContainerAwareInterface
 *
 * A Pimple container dependency can be injected to classes implementing this interface.
 *
 * @package Tmf\Container
 */
interface ContainerAwareInterface
{
    /**
     * Getter
     *
     * @return Container the pimple service container
     */
    public function getContainer();

    /**
     * Setter
     *
     * @param Container $container the pimple service container
     */
    public function setContainer(Container $container);
} 