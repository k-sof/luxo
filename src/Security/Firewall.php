<?php

namespace Luxo\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Firewall
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
     * @var RequestMatcher[]
     */
    private $accessControls;

    /**
     * Firewall constructor.
     *
     * @param TokenStorage $tokenStorage
     */
    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
        $this->accessControls = [
            [
                'path' => '^/admin',
                'role' => 'ADMIN',
            ],
        ];
    }

    public function match(Request $request)
    {
        $token = $this->tokenStorage->getToken();
        $tokenRoles = $token ? $token->getRoleNames() : ['ANONYMOUSLY'];

        foreach ($this->accessControls as $accessControl) {
            $path = $accessControl['path'] ?? null;
            $host = $accessControl['host'] ?? null;
            $method = $accessControl['method'] ?? null;
            $ips = $accessControl['ips'] ?? null;
            $role = $accessControl['role'] ?? 'ANONYMOUSLY';

            $matcher = new RequestMatcher($path, $host, $method, $ips);

            if ($matcher->matches($request) && !in_array($role, $tokenRoles)) {
                throw new AccessDeniedException();
            }
        }
    }
}
