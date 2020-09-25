<?php

namespace Luxo\EventListener;

use Luxo\Event\RequestEvent;
use Luxo\Security\Firewall;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestListener implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var Firewall
     */
    private $firewall;

    public function __construct(RequestStack $requestStack, Firewall $firewall)
    {
        $this->requestStack = $requestStack;
        $this->firewall = $firewall;
    }

    public function onKernelRequest(RequestEvent $requestEvent)
    {
        $request = $requestEvent->getRequest();
        $this->requestStack->push($request);

        $this->firewall->match($request);
    }

    public static function getSubscribedEvents()
    {
        return [
          RequestEvent::NAME => [
              ['onKernelRequest', -100],
          ],
        ];
    }
}
