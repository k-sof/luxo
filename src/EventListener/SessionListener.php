<?php

namespace Luxo\EventListener;

use Doctrine\ORM\EntityManager;
use Luxo\Event\RequestEvent;
use Luxo\Security\EntityUserProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SessionListener implements EventSubscriberInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var EntityUserProvider
     */
    private $provider;

    public function __construct(Session $session, TokenStorage $tokenStorage, EntityUserProvider $provider)
    {
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->provider = $provider;
    }

    public function onKernelRequest(RequestEvent $requestEvent)
    {
        $this->session->start();

        if ($this->session->has('token')) {
            /** @var TokenInterface $token */
            $token = $this->session->get('token');

            $user = $token->getUser();

            try {
                $user = $this->provider->refreshUser($user);
                $token->setUser($user);

                $this->tokenStorage->setToken($token);

            } catch (\Exception $exception) {
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
          RequestEvent::NAME => [
              ['onKernelRequest', -90],
          ],
        ];
    }
}
