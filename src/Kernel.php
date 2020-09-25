<?php

namespace Luxo;

use Luxo\DependencyInjection\Compiler\DoctrineAnnotationPass;
use Luxo\DependencyInjection\Compiler\EventSubscriberPass;
use Luxo\DependencyInjection\Compiler\FormPass;
use Luxo\DependencyInjection\Compiler\TwigPass;
use Luxo\DependencyInjection\FrameworkExtension;
use Luxo\Event\RequestEvent;
use Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator;
use Symfony\Bridge\ProxyManager\LazyProxy\PhpDumper\ProxyDumper;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\Debug\DebugClassLoader as LegacyDebugClassLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\ErrorHandler\DebugClassLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel
{
    /**
     * @var bool
     */
    private $booted;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var bool
     */
    private $debug;

    private static $freshCache = [];

    /**
     * Kernel constructor.
     *
     * @param $environment
     * @param $debug
     *
     * @throws \Exception
     */
    public function __construct($environment, $debug)
    {
        $this->environment = $environment;
        $this->debug = $debug;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['environment', 'debug'];
    }

    public function __wakeup()
    {
        $this->__construct($this->environment, $this->debug);
    }

    /**
     * @throws \Exception
     */
    public function boot()
    {
        $this->initializeContainer();

        $this->booted = true;
    }

    public function buildContainer()
    {
        $container = $this->getContainerBuilder();

        $this->configureContainer($container, $this->getContainerLoader($container));

        return $container;
    }

    /**
     * @throws \Exception
     */
    public function initializeContainer()
    {
        $class = $this->getContainerClass();
        $cacheDir = $this->getCacheDir();
        $cache = new ConfigCache($this->getCacheDir().'/'.$class.'.php', $this->debug);
        $cachePath = $cache->getPath();

        // Silence E_WARNING to ignore "include" failures - don't use "@" to prevent silencing fatal errors
        $errorLevel = error_reporting(\E_ALL ^ \E_WARNING);

        try {
            if (file_exists($cachePath) && \is_object($this->container = include $cachePath)
                && (!$this->debug || (self::$freshCache[$cachePath] ?? $cache->isFresh()))
            ) {
                self::$freshCache[$cachePath] = true;
                $this->container->set('kernel', $this);
                error_reporting($errorLevel);

                return;
            }
        } catch (\Throwable $e) {
        }

        $oldContainer = \is_object($this->container) ? new \ReflectionClass($this->container) : $this->container = null;

        try {
            is_dir($cacheDir) ?: mkdir($cacheDir, 0777, true);

            if ($lock = fopen($cachePath, 'w')) {
                chmod($cachePath, 0666 & ~umask());
                flock($lock, LOCK_EX | LOCK_NB, $wouldBlock);

                if (!flock($lock, $wouldBlock ? LOCK_SH : LOCK_EX)) {
                    fclose($lock);
                } else {
                    $cache = new class($cachePath, $this->debug) extends ConfigCache {
                        public $lock;

                        public function write(string $content, array $metadata = null)
                        {
                            rewind($this->lock);
                            ftruncate($this->lock, 0);
                            fwrite($this->lock, $content);

                            if (null !== $metadata) {
                                file_put_contents($this->getPath().'.meta', serialize($metadata));
                                @chmod($this->getPath().'.meta', 0666 & ~umask());
                            }

                            if (\function_exists('opcache_invalidate') && filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN)) {
                                @opcache_invalidate($this->getPath(), true);
                            }
                        }

                        public function release()
                        {
                            flock($this->lock, LOCK_UN);
                            fclose($this->lock);
                        }
                    };
                    $cache->lock = $lock;

                    if (!\is_object($this->container = include $cachePath)) {
                        $this->container = null;
                    } elseif (!$oldContainer || \get_class($this->container) !== $oldContainer->name) {
                        $this->container->set('kernel', $this);

                        return;
                    }
                }
            }
        } catch (\Throwable $e) {
        } finally {
            error_reporting($errorLevel);
        }

        if ($collectDeprecations = $this->debug && !\defined('PHPUNIT_COMPOSER_INSTALL')) {
            $collectedLogs = [];
            $previousHandler = set_error_handler(function ($type, $message, $file, $line) use (&$collectedLogs, &$previousHandler) {
                if (E_USER_DEPRECATED !== $type && E_DEPRECATED !== $type) {
                    return $previousHandler ? $previousHandler($type, $message, $file, $line) : false;
                }

                if (isset($collectedLogs[$message])) {
                    ++$collectedLogs[$message]['count'];

                    return null;
                }

                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
                // Clean the trace by removing first frames added by the error handler itself.
                for ($i = 0; isset($backtrace[$i]); ++$i) {
                    if (isset($backtrace[$i]['file'], $backtrace[$i]['line']) && $backtrace[$i]['line'] === $line && $backtrace[$i]['file'] === $file) {
                        $backtrace = \array_slice($backtrace, 1 + $i);
                        break;
                    }
                }
                // Remove frames added by DebugClassLoader.
                for ($i = \count($backtrace) - 2; 0 < $i; --$i) {
                    if (\in_array($backtrace[$i]['class'] ?? null, [DebugClassLoader::class, LegacyDebugClassLoader::class], true)) {
                        $backtrace = [$backtrace[$i + 1]];
                        break;
                    }
                }

                $collectedLogs[$message] = [
                    'type' => $type,
                    'message' => $message,
                    'file' => $file,
                    'line' => $line,
                    'trace' => [$backtrace[0]],
                    'count' => 1,
                ];

                return null;
            });
        }

        try {
            $container = null;
            $container = $this->buildContainer();
            $container->compile();
        } finally {
            if ($collectDeprecations) {
                restore_error_handler();

                file_put_contents($cacheDir.'/'.$class.'Deprecations.log', serialize(array_values($collectedLogs)));
                file_put_contents($cacheDir.'/'.$class.'Compiler.log', null !== $container ? implode("\n", $container->getCompiler()->getLog()) : '');
            }
        }

        $this->dumpContainer($cache, $container, $class, $this->getContainerBaseClass());
        if (method_exists($cache, 'release')) {
            $cache->release();
        }

        $this->container = require $cachePath;
        $this->container->set('kernel', $this);

        if ($oldContainer && \get_class($this->container) !== $oldContainer->name) {
            // Because concurrent requests might still be using them,
            // old container files are not removed immediately,
            // but on a next dump of the container.
            static $legacyContainers = [];
            $oldContainerDir = \dirname($oldContainer->getFileName());
            $legacyContainers[$oldContainerDir.'.legacy'] = true;
            foreach (glob(\dirname($oldContainerDir).\DIRECTORY_SEPARATOR.'*.legacy', GLOB_NOSORT) as $legacyContainer) {
                if (!isset($legacyContainers[$legacyContainer]) && @unlink($legacyContainer)) {
                    (new Filesystem())->remove(substr($legacyContainer, 0, -7));
                }
            }

            touch($oldContainerDir.'.legacy');
        }
    }

    public function loadRoutes(LoaderInterface $loader)
    {
        $routes = new RouteCollectionBuilder($loader);

        $confDir = $this->getProjectDir().'/config';

        $routes->import($confDir.'/{routes}/'.$this->environment.'/*.php', '/', 'glob');
        $routes->import($confDir.'/{routes}/*.php', '/', 'glob');
        $routes->import($confDir.'/{routes}.php', '/', 'glob');

        return $routes->build();
    }

    public function getResourcesDir()
    {
        return $this->getProjectDir().'/resources';
    }

    public function getPublicDir()
    {
        return $this->getProjectDir().'/public';
    }

    public function getStorageDir()
    {
        return $this->getProjectDir().'/storage';
    }

    public function getCacheDir()
    {
        return $this->getProjectDir().'/var/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return $this->getProjectDir().'/var/log';
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }

    /**
     * @param ContainerBuilder $container
     * @param LoaderInterface  $loader
     *
     * @throws \Exception
     */
    public function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->getParameterBag()->add($this->getKernelParameters());

        if (!$container->hasDefinition('kernel')) {
            $container->register('kernel', static::class)
              ->setSynthetic(true)
              ->setPublic(true)
          ;
        }

        $kernelDefinition = $container->getDefinition('kernel');
        $kernelDefinition->addTag('router.loader');

        $container->registerExtension(new FrameworkExtension());

        $loader->load('config/{services}.php', 'glob');
        $loader->load('config/{services}_'.$this->environment . '.php', 'glob');

        $container->addCompilerPass(new DoctrineAnnotationPass());
        $container->addCompilerPass(new EventSubscriberPass());
        $container->addCompilerPass(new AddConsoleCommandPass());
        $container->addCompilerPass(new TwigPass());
        $container->addCompilerPass(new FormPass());
    }

    /**
     * @throws \Exception
     */
    public function handle(Request $request)
    {
        $this->boot();

        $eventDispatcher = $this->container->get(EventDispatcher::class);
        $event = new RequestEvent($request);
        $eventDispatcher->dispatch($event, RequestEvent::NAME);

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        $action = $request->attributes->get('_route')['action'];

        $arguments = $request->attributes->get('_route')['parameters'];

        $reflectedAction = new \ReflectionClass($action);
        $reflectedActionMethod = $reflectedAction->getMethod('__invoke');

        foreach ($reflectedActionMethod->getParameters() as $reflectionParameter) {
            if (
        $reflectionParameter->getType() instanceof \ReflectionNamedType
      ) {
                if ($reflectionParameter->getType()->allowsNull() && !$this->container->has($reflectionParameter->getType()->getName())) {
                    continue;
                }

                $arguments[$reflectionParameter->getName()] = $this->container->get($reflectionParameter->getType()->getName());
            }
        }

        /** @var Response $response */
        $response = $reflectedAction->getMethod('__invoke')->invokeArgs(
          $this->container->get($action),
          $arguments
        );

        return $response;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getContainerLoader(ContainerBuilder $container)
    {
        $locator = new FileLocator(__DIR__ . '/../');

        $resolver = new LoaderResolver([
            new PhpFileLoader($container, $locator),
            new GlobFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
            new ClosureLoader($container),
        ]);

        return new DelegatingLoader($resolver);
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * Gets a new ContainerBuilder instance used to build the service container.
     *
     * @return ContainerBuilder
     */
    protected function getContainerBuilder()
    {
        $container = new ContainerBuilder();
        $container->getParameterBag()->add($this->getKernelParameters());

        if ($this instanceof CompilerPassInterface) {
            $container->addCompilerPass($this, PassConfig::TYPE_BEFORE_OPTIMIZATION, -10000);
        }
        if (class_exists('ProxyManager\Configuration') && class_exists('Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator')) {
            $container->setProxyInstantiator(new RuntimeInstantiator());
        }

        return $container;
    }

    protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, string $class, string $baseClass)
    {
        // cache the container
        $dumper = new PhpDumper($container);

        if (class_exists('ProxyManager\Configuration') && class_exists('Symfony\Bridge\ProxyManager\LazyProxy\PhpDumper\ProxyDumper')) {
            $dumper->setProxyDumper(new ProxyDumper());
        }

        $content = $dumper->dump([
            'class' => $class,
            'base_class' => $baseClass,
            'file' => $cache->getPath(),
            'as_files' => true,
            'debug' => $this->debug,
            'build_time' => $container->hasParameter('kernel.container_build_time') ? $container->getParameter('kernel.container_build_time') : time(),
        ]);

        $rootCode = array_pop($content);
        $dir = \dirname($cache->getPath()).'/';
        $fs = new Filesystem();

        foreach ($content as $file => $code) {
            $fs->dumpFile($dir.$file, $code);
            @chmod($dir.$file, 0666 & ~umask());
        }
        $legacyFile = \dirname($dir.key($content)).'.legacy';
        if (file_exists($legacyFile)) {
            @unlink($legacyFile);
        }

        $cache->write($rootCode, $container->getResources());
    }

    /**
     * Gets the container class.
     *
     * @throws \InvalidArgumentException If the generated classname is invalid
     *
     * @return string The container class
     */
    protected function getContainerClass()
    {
        $class = \get_class($this);
        $class = 'c' === $class[0] && 0 === strpos($class, "class@anonymous\0") ? get_parent_class($class).str_replace('.', '_', ContainerBuilder::hash($class)) : $class;
        $class = str_replace('\\', '_', $class).ucfirst($this->environment).($this->debug ? 'Debug' : '').'Container';
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $class)) {
            throw new \InvalidArgumentException(sprintf('The environment "%s" contains invalid characters, it can only contain characters allowed in PHP class names.', $this->environment));
        }

        return $class;
    }

    /**
     * Gets the container's base class.
     *
     * All names except Container must be fully qualified.
     *
     * @return string
     */
    protected function getContainerBaseClass()
    {
        return 'Container';
    }

    private function getKernelParameters()
    {
        return [
        'kernel.project_dir' => realpath($this->getProjectDir()) ?: $this->getProjectDir(),
        'kernel.environment' => $this->environment,
        'kernel.debug' => $this->debug,
        'kernel.cache_dir' => realpath($this->getCacheDir()) ?: $this->getCacheDir(),
        'kernel.log_dir' => realpath($this->getLogDir()) ?: $this->getLogDir(),
        'kernel.storage_dir' => realpath($this->getStorageDir()) ?: $this->getStorageDir(),
        'kernel.resources_dir' => realpath($this->getResourcesDir()) ?: $this->getResourcesDir(),
        'kernel.public_dir' => realpath($this->getPublicDir()) ?: $this->getPublicDir(),
      ];
    }
}
