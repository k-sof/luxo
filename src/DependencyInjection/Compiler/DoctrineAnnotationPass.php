<?php

namespace Luxo\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineAnnotationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(AnnotationRegistry::class)) {
            return;
        }

        $container->get(AnnotationRegistry::class)->registerUniqueLoader('class_exists');
    }
}
