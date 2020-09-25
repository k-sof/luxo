<?php

namespace Luxo\Action\Admin;

use Luxo\Action\Action;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardAction extends Action
{
    /**
     * @Route(path="/admin")
     */
    public function __invoke()
    {
        return new Response('adminazdazdaza ');
    }
}
