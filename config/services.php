<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator) {
    $configurator->extension('framework', [
        'asset' => [
            'manifest' => '%kernel.public_dir%/assets/manifest.json',
        ],
        'router' => [
            'annotation' => true,
        ],
        'doctrine' => [
            'dsn' => '%env(DATABASE_DSN)%',
            'entities_dir' => '%kernel.project_dir%/src/Entity',
        ],
        'twig' => [
            'paths' => ['%kernel.resources_dir%/views'],
        ],
        'session' => [
            'enabled' => true,
        ],
        'mailer' => [
            'dsn' => '%env(MAILER_DSN)%',
        ],
    ]);

    $services = $configurator
      ->services()
      ->defaults()
      ->autowire()
      ->autoconfigure()
    ;

    $services
      ->load('Luxo\\', '../src/*')
      ->exclude('../src/{Console,DependencyInjection,Entity,Routing,Kernel.php,Event,Security}')
    ;
};
