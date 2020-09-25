<?php

namespace Luxo\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventSubscriberPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(EventDispatcher::class)) {
            return;
        }

        foreach ($container->findTaggedServiceIds('event.subscriber') as $id => $tags) {
            $container->getDefinition(EventDispatcher::class)->addMethodCall('addSubscriber', [new Reference($id)]);
        }
    }
}
