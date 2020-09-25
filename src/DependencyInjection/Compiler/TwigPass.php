<?php

namespace Luxo\DependencyInjection\Compiler;

use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\Translator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\ContainerRuntimeLoader;

class TwigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has(Packages::class)) {
            $container
                ->register(AssetExtension::class, AssetExtension::class)
                ->setArguments([
                    new Reference(Packages::class),
                ])
                ->addTag('twig.extension')
            ;
        }

        if ($container->has(FormFactory::class)) {
            $container
                ->register(FormExtension::class, FormExtension::class)
                ->addTag('twig.extension')
            ;
            $container
                ->register(FormRenderer::class, FormRenderer::class)
                ->setArguments([
                    new Reference(TwigRendererEngine::class),
                ])
                ->addTag('twig.runtime')
            ;
            $container
                ->register(TwigRendererEngine::class, TwigRendererEngine::class)
                ->setArguments([
                    ['bootstrap_4_layout.html.twig'],
                    new Reference(Environment::class),
                ]);
            $appVariableReflection = new \ReflectionClass('\Symfony\Bridge\Twig\AppVariable');
            $vendorTwigBridgeDirectory = dirname($appVariableReflection->getFileName());
            $container->getDefinition(FilesystemLoader::class)
                ->addMethodCall('addPath', [$vendorTwigBridgeDirectory.'/Resources/views/Form']);
        }

        if ($container->has(Router::class)) {
            $container
                ->register(RoutingExtension::class, RoutingExtension::class)
                ->setArguments([
                   new Reference(Router::class),
                ])
                ->addTag('twig.extension')
            ;
        }

        if ($container->has(Translator::class)) {
            $container->register(TranslationExtension::class, TranslationExtension::class)
                ->setArguments([
                    new Reference(Translator::class),
                ])
                ->addTag('twig.extension');
        }
        foreach ($container->findTaggedServiceIds('twig.extension') as $extension => $tags) {
            $container->getDefinition(\Twig\Environment::class)->addMethodCall('addExtension', [new Reference($extension)]);
        }

        $definition = $container->getDefinition(ContainerRuntimeLoader::class);

        $mapping = [];

        foreach ($container->findTaggedServiceIds('twig.runtime', true) as $id => $attributes) {
            $def = $container->getDefinition($id);
            $mapping[$def->getClass()] = new Reference($id);
        }

        $definition->setArgument(0, ServiceLocatorTagPass::register($container, $mapping));
    }
}
