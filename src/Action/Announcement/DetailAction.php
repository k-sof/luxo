<?php

namespace Luxo\Action\Announcement;

use Luxo\Action\Action;
use Luxo\Repository\AnnouncementRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DetailAction extends Action
{
    /**
     * @Route(path="/announcement/{id}")
     *
     * @param $id
     * @param AnnouncementRepository $announcementRepository
     *
     * @return Response
     */
    public function __invoke($id, AnnouncementRepository $announcementRepository)
    {
        return $this->render('Announcement/Detail.html.twig', [
            'announcement' => $announcementRepository->find($id),
        ]);
    }
}
