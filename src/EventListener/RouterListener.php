<?php

namespace Luxo\EventListener;

use Luxo\Event\RequestEvent;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Router;

class RouterListener implements EventSubscriberInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router, Logger $logger = null)
    {
        $this->router = $router;
        $this->logger = $logger;
    }

    public function onKernelRequest(RequestEvent $requestEvent)
    {
        $request = $requestEvent->getRequest();

        $parameters = $this->router->matchRequest($requestEvent->getRequest());

        if (null !== $this->logger) {
            $this->logger->info('Matched route "{route}".', [
                'route' => isset($parameters['_route']) ? $parameters['_route'] : 'n/a',
                'route_parameters' => $parameters,
                'request_uri' => $request->getUri(),
                'method' => $request->getMethod(),
            ]);
        }

        $attributes = [];
        $attributes['_route']['name'] = $parameters['_route'];
        $attributes['_route']['action'] = $parameters['_action'];
        unset($parameters['_route'], $parameters['_action']);
        $attributes['_route']['parameters'] = $parameters;

        $request->attributes->add($attributes);
    }

    public static function getSubscribedEvents()
    {
        return [
          RequestEvent::NAME => [
              ['onKernelRequest', -80],
          ],
        ];
    }
}
