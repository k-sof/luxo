<?php


namespace Luxo\Action\Myannouncement;

use Doctrine\ORM\EntityManager;
use Luxo\Action\Action;
use Luxo\Entity\Announcement;
use Luxo\Repository\AnnouncementRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ShowAction extends Action
{
    /**
     * @Route(path="/user/myannouncements")
     * @param TokenStorage $tokenStorage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(TokenStorage $tokenStorage, EntityManager $manager, AnnouncementRepository $announcementRepository)
    {
        return $this->render('myannouncement.html.twig', [
            'list_announcements' => $announcementRepository->findByUser($tokenStorage->getToken()->getUser()),
        ]);

    }
}
