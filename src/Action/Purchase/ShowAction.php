<?php

namespace Luxo\Action\Purchase;

use Luxo\Action\Action;
use Luxo\Repository\AnnouncementRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class ShowAction extends Action
{
    /**
     * @Route(path="/purchase")
     *
     * @param AnnouncementRepository $announcementRepository
     *
     * @param Session $session
     * @return Response
     */
    public function __invoke(AnnouncementRepository $announcementRepository, Session $session)
    {
        return $this->render('purchase.html.twig', [
            'announcements_achat' => $announcementRepository->findAchatAll(),
            'session' => $session->get('token') ? $session->get('token')->getUser(): "",
        ]);
    }
}
