<?php

namespace Luxo\Action\Security;

use Luxo\Action\Action;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class LogoutAction extends Action
{
    /**
     * @Route(path="/logout")
     *
     * @param Session $session
     */
    public function __invoke(Session $session)
    {
        $session->remove('token');

        return $this->redirectToRoute('luxo_security_login');
    }
}
