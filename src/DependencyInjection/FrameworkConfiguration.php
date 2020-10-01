<?php

namespace Luxo\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;

class FrameworkConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('framework');

        $treeBuilder->getRootNode()
          ->addDefaultsIfNotSet()
          ->children()
              ->booleanNode('enabled')->defaultTrue()->end()
              ->append($this->addSessionConfiguration())
              ->append($this->addAssetConfiguration())
              ->append($this->addRouterConfiguration())
              ->append($this->addDoctrineConfiguration())
              ->append($this->addTwigConfiguration())
              ->append($this->addMailerConfiguration())
          ->end()
        ;

        return $treeBuilder;
    }

    private function addAssetConfiguration()
    {
        $treeBuilder = new TreeBuilder('asset');

        $treeBuilder->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('path')->defaultValue('/')->end()
                ->scalarNode('urls')->defaultNull()->end()
                ->scalarNode('manifest')->defaultNull()->end()
            ->end()
        ;

        return $treeBuilder->getRootNode();
    }

    private function addSessionConfiguration()
    {
        $treeBuilder = new TreeBuilder('session');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->arrayNode('options')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('handler')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('type')->defaultValue(NativeFileSessionHandler::class)->end()
                        ->scalarNode('path')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder->getRootNode();
    }

    private function addRouterConfiguration()
    {
        $treeBuilder = new TreeBuilder('router');

        $treeBuilder->getRootNode()
            ->isRequired()
            ->children()
                ->scalarNode('annotation')->defaultTrue()->end()
            ->end()
        ;

        return $treeBuilder->getRootNode();
    }

    private function addDoctrineConfiguration()
    {
        $treeBuilder = new TreeBuilder('doctrine');

        $treeBuilder->getRootNode()
            ->isRequired()
            ->children()
                ->scalarNode('dsn')->isRequired()->end()
                ->scalarNode('entities_dir')->isRequired()->end()
            ->end()
        ;

        return $treeBuilder->getRootNode();
    }

    private function addTwigConfiguration()
    {
        $treeBuilder = new TreeBuilder('twig');

        $treeBuilder->getRootNode()
            ->isRequired()
                ->children()
                ->arrayNode('paths')->isRequired()->scalarPrototype()->end()
            ->end()
        ;

        return $treeBuilder->getRootNode();
    }

    private function addTranslationConfiguration()
    {
    }

    private function addSecurityConfiguration()
    {
        $treeBuilder = new TreeBuilder('security');

        $treeBuilder->getRootNode()
            ->isRequired()
            ->children()
                ->arrayNode('firewalls')->isRequired()->scalarPrototype()->end()
            ->end()
        ;

        return $treeBuilder->getRootNode();
    }

    private function addMailerConfiguration()
    {
        $treeBuilder = new TreeBuilder('mailer');

        $treeBuilder->getRootNode()
            ->isRequired()
            ->children()
                ->scalarNode('dsn')->isRequired()->end()
            ->end()
        ;

        return $treeBuilder->getRootNode();
    }
}
