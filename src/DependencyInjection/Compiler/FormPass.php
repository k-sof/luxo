<?php

namespace Luxo\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FormPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(FormFactoryBuilder::class)) {
            return;
        }

        if (class_exists(Request::class)) {
            $container
                ->register(HttpFoundationExtension::class, HttpFoundationExtension::class)
                ->addTag('form.extension')
            ;
        }

        if ($container->has(ValidatorInterface::class)) {
            $container
               ->register(ValidatorExtension::class, ValidatorExtension::class)
               ->setArguments([new Reference(ValidatorInterface::class)])
               ->addTag('form.extension')
           ;
        }

        foreach ($container->findTaggedServiceIds('form.extension') as $extension => $tags) {
            $container
                ->getDefinition(FormFactoryBuilder::class)
                ->addMethodCall('addExtension', [new Reference($extension)])
            ;
        }
    }
}
