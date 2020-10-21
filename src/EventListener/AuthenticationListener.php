<?php

namespace Luxo\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

class AuthenticationListener implements EventSubscriberInterface
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;
    /**
     * @var Session
     */
    private $session;

    public function __construct(TokenStorage $tokenStorage, Session $session)
    {
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {

        $this->tokenStorage->setToken($event->getAuthenticationToken());
        $this->session->set('token', $this->tokenStorage->getToken());
    }

    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => [
                ['onAuthenticationSuccess', -10],
            ],
        ];
    }
}
