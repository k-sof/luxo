<?php

namespace Luxo\Action\Rental;

use Luxo\Action\Action;
use Luxo\Repository\AnnouncementRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class ShowAction extends Action
{
    /**
     * @Route(path="/rental")
     *
     * @param AnnouncementRepository $announcementRepository
     *
     * @param Session $session
     * @return Response
     */
    public function __invoke(AnnouncementRepository $announcementRepository, Session $session)
    {
        return $this->render('rental.html.twig', [
            'announcements_location' => $announcementRepository->findLocationAll(),
            'session' => $session->get('token') ? $session->get('token')->getUser(): "",
        ]);
    }
}
