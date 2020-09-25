<?php

namespace Luxo\Action;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Route;

class RedirectAction extends Action
{
    public function __invoke(RequestStack $requestStack, UrlGenerator $urlGenerator)
    {
        /** @var Route $route */
        $route = $requestStack->getCurrentRequest()->attributes->get('_route');

        return new RedirectResponse();
    }
}
