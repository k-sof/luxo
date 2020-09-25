<?php

namespace Luxo\Action\Home;

use Luxo\Action\Action;
use Luxo\Repository\AnnouncementRepository;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ShowAction extends Action
{
    /**
     * @Route(path="/")
     * @Route(path="/home")
     *
     * @param AnnouncementRepository $announcementRepository
     * @param TokenStorage           $tokenStorage
     * @param Session                $session
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(AnnouncementRepository $announcementRepository, TokenStorage $tokenStorage, Session $session)
    {
        return $this->render('home.html.twig', [
            'announcements' =>[
                'location' => $announcementRepository->findByLocationWithImages(),
                'achat' => $announcementRepository->findByAchatWithImages()
            ],
            'session' => $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null,
        ]);
    }
}
