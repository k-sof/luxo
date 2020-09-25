<?php

namespace Luxo\Routing;

use Symfony\Component\Routing\Loader\AnnotationClassLoader;
use Symfony\Component\Routing\Route;

class AnnotatedRouteActionLoader extends AnnotationClassLoader
{
    /**
     * Configures the _controller default parameter of a given Route instance.
     *
     * @param mixed $annot The annotation class instance
     */
    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, $annot)
    {
        if ('__invoke' === $method->getName()) {
            $route->setDefault('_action', $class->getName());
        } else {
            $route->setDefault('_action', $class->getName() . '::' . $method->getName());
        }
    }

    /**
     * Makes the default route name more sane by removing common keywords.
     *
     * @return string
     */
    protected function getDefaultRouteName(\ReflectionClass $class, \ReflectionMethod $method)
    {
        return preg_replace([
            '/(action)_/',
            '/action(_\d+)?$/',
            '/__/',
            '/__invoke/',
        ], [
            '_',
            '\\1',
            '_',
            '',
        ], parent::getDefaultRouteName($class, $method));
    }
}
