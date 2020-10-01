<?php

namespace Luxo\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Luxo\Entity\User;
use Luxo\Repository\UserRepository;
use Luxo\Routing\AnnotatedRouteActionLoader;
use Luxo\Security\EntityUserProvider;
use Luxo\Security\Firewall;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\DoctrineProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension as AbstractExtension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Transports;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Routing\Loader\ContainerLoader;
use Symfony\Component\Routing\Loader\DirectoryLoader;
use Symfony\Component\Routing\Loader\GlobFileLoader;
use Symfony\Component\Routing\Loader\ObjectLoader;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\ContainerRuntimeLoader;

class FrameworkExtension extends AbstractExtension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configs = $this->processConfiguration(new FrameworkConfiguration(), $configs);

        $container->register(EventDispatcher::class, EventDispatcher::class)->setPublic(true);
        $container->setAlias('event_dispatcher', EventDispatcher::class);

        $container->registerForAutoconfiguration(EventSubscriberInterface::class)->addTag('event.subscriber');

        $container->register(RequestStack::class, RequestStack::class)->setPublic(true);
        $container->setAlias('request_stack', RequestStack::class);

        $container
            ->register(AnnotationRegistry::class, AnnotationRegistry::class)
        ;

        $container->register(AnnotationReader::class, AnnotationReader::class);
        $container->setAlias('doctrine.annotation_reader', AnnotationReader::class);

        $container->registerForAutoconfiguration(Command::class)->addTag('console.command');

        $this->configureAsset($container, $configs['asset']);
        $this->configureRouter($container, $configs['router']);
        $this->configureDoctrine($container, $configs['doctrine']);
        $this->configureSession($container, $configs['session']);
        $this->configureTwig($container, $configs['twig']);
        $this->configureForm($container);
        $this->configureValidator($container);
        $this->configureTranslation($container);
        $this->configureSecurity($container);
        $this->configureMailer($container, $configs['mailer']);
    }

    public function getAlias()
    {
        return 'framework';
    }

    public function configureForm(ContainerBuilder $container)
    {
        $container
            ->register(FormFactoryBuilder::class, FormFactoryBuilder::class)
            ->setArguments([
                true,
            ])
        ;
        $container
            ->register(FormFactory::class, FormFactory::class)
            ->setFactory([
                new Reference(FormFactoryBuilder::class),
                'getFormFactory',
            ])
            ->setPublic(true)
        ;
    }

    private function configureAsset(ContainerBuilder $container, array $configs)
    {
        $container
            ->register(EmptyVersionStrategy::class, EmptyVersionStrategy::class)
        ;
        $container->setAlias('asset.strategy', EmptyVersionStrategy::class);

        $container
            ->register(RequestStackContext::class, RequestStackContext::class)
            ->setArguments([
                new Reference(RequestStack::class),
            ])
        ;

        if ($configs['manifest']) {
            $container
                ->register(JsonManifestVersionStrategy::class, JsonManifestVersionStrategy::class)
                ->setArguments([
                    $configs['manifest'],
                ])
            ;
            $container->setAlias('asset.strategy', JsonManifestVersionStrategy::class);
        }

        if ($configs['path']) {
            $container
                ->register(PathPackage::class, PathPackage::class)
                ->setArguments([
                    $configs['path'],
                    new Reference('asset.strategy'),
                ]);

            $container->setAlias('asset.default_package', PathPackage::class);
        }

        if ($configs['urls']) {
            $container
                ->register(UrlPackage::class, UrlPackage::class)
                ->setArguments([
                    new Reference('asset.strategy'),
                    new Reference(RequestStackContext::class),
                ]);

            $container->setAlias('asset.default_package', UrlPackage::class);
        }

        $container
            ->register(Packages::class, Packages::class)
            ->setArguments([
                new Reference('asset.default_package'),
            ])
        ;
    }

    private function configureRouter(ContainerBuilder $container, array $configs)
    {
        $container
          ->register(FileLocator::class, FileLocator::class)
          ->setArguments([
            '%kernel.project_dir%',
          ])
        ;

        $container->setAlias('router.locator', FileLocator::class);
        $container->setAlias(FileLocatorInterface::class, FileLocator::class);

        $container
          ->register(PhpFileLoader::class, PhpFileLoader::class)
          ->setAutowired(true)
          ->addTag('router.loader')
        ;
        $container->setAlias('router.php_file_loader', PhpFileLoader::class);

        $container
          ->register(GlobFileLoader::class, GlobFileLoader::class)
          ->setAutoconfigured(true)
          ->setAutowired(true)
          ->addTag('router.loader')
        ;

        $container->setAlias('router.directory_loader', GlobFileLoader::class);

        $container
          ->register(DirectoryLoader::class, DirectoryLoader::class)
          ->setAutoconfigured(true)
          ->setAutowired(true)
          ->addTag('router.loader')
        ;

        $container
          ->register(ContainerLoader::class, ContainerLoader::class)
          ->setAutoconfigured(true)
          ->setAutowired(true)
          ->addTag('router.loader')
        ;

        $container->setAlias('router.service_loader', ObjectLoader::class);

        $container
          ->register(AnnotatedRouteActionLoader::class, AnnotatedRouteActionLoader::class)
          ->setArguments([
            new Reference('doctrine.annotation_reader'),
          ])
          ->addTag('router.loader')
        ;

        $container->setAlias('router.action_loader', AnnotatedRouteActionLoader::class);

        $container
          ->register(AnnotationDirectoryLoader::class, AnnotationDirectoryLoader::class)
          ->setArguments([
            new Reference('router.locator'),
            new Reference('router.action_loader'),
          ])
          ->addTag('router.loader')
        ;

        $container
          ->register(LoaderResolver::class, LoaderResolver::class)
          ->setArguments([array_map(function ($id) {
              return new Reference($id);
          }, array_keys($container->findTaggedServiceIds('router.loader')))])
        ;
        $container->setAlias('router.loader_resolver', LoaderResolver::class);

        $container
          ->register(DelegatingLoader::class, DelegatingLoader::class)
          ->setArguments([new Reference('router.loader_resolver')])
        ;

        $container->setAlias('routing.loader', DelegatingLoader::class);

        $container
          ->register(Router::class, Router::class)
          ->setArguments([
            new Reference('routing.loader'),
            'kernel::loadRoutes',
            [
              'debug' => '%kernel.environment%',
              'cache_dir' => '%kernel.cache_dir%/routing/',
              'resource_type' => 'service',
            ],
          ])
        ;

        $container->setAlias('router', Router::class)->setPublic(true);
        $container->setAlias('routing.loader', DelegatingLoader::class);

        $container->register(RequestContext::class, RequestContext::class);

        $container
          ->register(RequestMatcher::class, RequestMatcher::class)
          ->setFactory(new Reference('router'))
          ->addMethodCall('getMatcher');

        $container
            ->register(UrlGenerator::class, UrlGenerator::class)
            ->setFactory([new Reference(Router::class), 'getGenerator'])
        ;
    }

    private function configureDoctrine(ContainerBuilder $container, array $configs)
    {
        $container
          ->register(AnnotationDriver::class, AnnotationDriver::class)
          ->setArguments([
            new Reference('doctrine.annotation_reader'),
            $configs['entities_dir'],
          ])
        ;

        $container->setAlias('doctrine.metadata_driver', AnnotationDriver::class);

        $container
            ->register(FilesystemAdapter::class, FilesystemAdapter::class)
            ->setArguments([
              '',
              0,
              '%kernel.cache_dir%/doctrine/',
            ])
        ;

        $container->setAlias('doctrine.cache_filesystem', FilesystemAdapter::class);

        $container
          ->register(DoctrineProvider::class, DoctrineProvider::class)
          ->setArguments([
            new Reference('doctrine.cache_filesystem'),
          ])
        ;

        $container->setAlias('doctrine.cache_provider', DoctrineProvider::class);

        $container
          ->register(Configuration::class, Configuration::class)
          ->addMethodCall('setMetadataCacheImpl', [new Reference('doctrine.cache_provider')])
          ->addMethodCall('setQueryCacheImpl', [new Reference('doctrine.cache_provider')])
          ->addMethodCall('setMetadataDriverImpl', [new Reference('doctrine.metadata_driver')])
          ->addMethodCall('setProxyDir', ['%kernel.cache_dir%/proxies'])
          ->addMethodCall('setProxyNamespace', ['DoctrineProxy'])
        ;

        $container->setAlias('doctrine.configuration', Configuration::class)->setPublic(true);

        $container
          ->register(Connection::class, Connection::class)
          ->setFactory([DriverManager::class, 'getConnection'])
          ->setArguments([
            ['url' => $container->resolveEnvPlaceholders($configs['dsn'], true)],
          ])
        ;

        $container->setAlias('doctrine.connection', Connection::class)->setPublic(true);

        $container
            ->register(EntityManager::class, EntityManager::class)
            ->setFactory([EntityManager::class, 'create'])
            ->setArguments([
              new Reference('doctrine.connection'),
              new Reference('doctrine.configuration'),
            ])
            ->setPublic(true)
        ;

        $container->setAlias('doctrine.entity_manager', EntityManager::class);
        $container->setAlias('doctrine', EntityManager::class);
    }

    private function configureTwig(ContainerBuilder $container, array $configs)
    {
        $container
          ->register(FilesystemLoader::class, FilesystemLoader::class)
          ->setArguments([
            '$paths' => $configs['paths'],
          ])
        ;

        $container->setAlias('twig.loader', FilesystemLoader::class);

        $container->register(ContainerRuntimeLoader::class, ContainerRuntimeLoader::class);

        $container
          ->register(\Twig\Environment::class, \Twig\Environment::class)
          ->setArguments([
            new Reference('twig.loader'),
              [
                  'debug' => '%kernel.debug%',
                  'cache' => '%kernel.cache_dir%/twig',
                  'auto_reload' => '%kernel.debug%',
              ],
          ])
            ->addMethodCall('addRuntimeLoader', [new Reference(ContainerRuntimeLoader::class)])
        ;

        $container->setAlias('twig', \Twig\Environment::class)->setPublic(true);
    }

    private function configureSession(ContainerBuilder $container, array $configs)
    {
        if (!$container->has($configs['handler']['type'])) {
            $container
                ->register($configs['handler']['type'], $configs['handler']['type'])
            ;
        }

        $container->setAlias('session.handler', $configs['handler']['type']);

        $container
            ->register(NativeSessionStorage::class, NativeSessionStorage::class)
            ->setArguments([
                $configs['options'],
                new Reference('session.handler'),
            ])
        ;

        $container->setAlias('session.storage', NativeSessionStorage::class);

        $container
            ->register(Session::class, Session::class)
            ->setArguments([
                new Reference('session.storage'),
            ])
            ->setPublic(true)
        ;

        $container->setAlias('session', Session::class);
    }

    private function configureValidator(ContainerBuilder $container)
    {
        $container
            ->register(ValidatorBuilder::class, ValidatorBuilder::class)
        ;
        $container
            ->getDefinition(ValidatorBuilder::class)
            ->addMethodCall('enableAnnotationMapping')
        ;
        $container
            ->register(ValidatorInterface::class, ValidatorInterface::class)
            ->setFactory([new Reference(ValidatorBuilder::class), 'getValidator'])
            ->setPublic(true)
        ;
    }

    private function configureTranslation(ContainerBuilder $container)
    {
        $container->register(Translator::class, Translator::class)
            ->setArguments([
               'fr_FR',
            ]);
    }

    private function configureSecurity(ContainerBuilder $container)
    {
        $container
            ->register(TokenStorage::class, TokenStorage::class)
            ->setPublic(true)
        ;

        $container
            ->register(UserChecker::class, UserChecker::class)
        ;

        $container->register(EntityUserProvider::class, EntityUserProvider::class)
            ->setArguments([
                new Reference(UserRepository::class),
                'email',
            ])
            ->setPublic(true)
        ;

        $container
            ->register(EncoderFactory::class, EncoderFactory::class)
            ->setArguments([
                [User::class => ['algorithm' => 'sodium']],
            ])
        ;

        $container
            ->register(DaoAuthenticationProvider::class, DaoAuthenticationProvider::class)
            ->setArguments([
                new Reference(EntityUserProvider::class),
                new Reference(UserChecker::class),
                User::class,
                new Reference(EncoderFactory::class),
            ])
            ->setPublic(true)
        ;

        $container
            ->register(Firewall::class, Firewall::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
        ;

        $container
            ->register(AuthenticationProviderManager::class, AuthenticationProviderManager::class)
            ->setArguments([
                [new Reference(DaoAuthenticationProvider::class)],
            ])
            ->addMethodCall('setEventDispatcher', [new Reference(EventDispatcher::class)])
            ->setPublic(true)
        ;
    }

    private function configureMailer(ContainerBuilder $container, array $config)
    {
        $container
            ->register(Transport::class, Transport::class)
            ->setFactory([Transport::class, 'fromDsn'])
            ->setArguments([$config['dsn']])
        ;

        $container
            ->register(Transports::class, Transports::class)
            ->setArguments(
                [[new Reference(Transport::class), 'getDefaultFactories']]
            );

        $container
            ->register(Mailer::class, Mailer::class)
            ->setArguments([
                new Reference(Transports::class),
            ])
        ->setPublic(true);
    }
}
