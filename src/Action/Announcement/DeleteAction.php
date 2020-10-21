<?php


namespace Luxo\Action\Announcement;



use Luxo\Action\Action;
use Luxo\Repository\AnnouncementRepository;
use Luxo\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class DeleteAction extends Action
{
    /**
     * @Route("announcement/delete/{id}")
     * @param $id
     * @param AnnouncementRepository $announcementRepository
     * @param TokenStorage $tokenStorage
     * @return Response
     */
    public function __invoke($id, AnnouncementRepository $announcementRepository, TokenStorage $tokenStorage)
    {
        /*
         * verifier l'annonce qui correspond a l'utilisateur
         */

        $announcementRepository->delete($id);
        /*

        */

        return $this->redirectToRoute('luxo_announcement_list');

    }
}
