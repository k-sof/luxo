<?php


namespace Luxo\Action\Newannouncement;


use Luxo\Action\Action;
use Luxo\Entity\Announcement;
use Luxo\Form\AddAnnouncementForm;
use Luxo\Repository\AnnouncementRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ShowAction extends Action
{
    /**
     * @Route(path="/user/new")
     * @param FormFactory $formFactory
     * @param RequestStack $requestStack
     * @param AnnouncementRepository $announcementRepository
     * @param TokenStorage $tokenStorage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(FormFactory $formFactory, RequestStack $requestStack, AnnouncementRepository $announcementRepository, TokenStorage $tokenStorage)
    {
        if(!$tokenStorage->getToken()){
            throw new Exception("Veuillez vous connecter");
        }
        $announcement = new Announcement();
        $form = $formFactory->createBuilder(AddAnnouncementForm::class, $announcement)
            ->getForm();
        $form->handleRequest($requestStack->getCurrentRequest());
        if($form->isSubmitted() && $form->isValid()){
            /** @var Announcement $announcement */
            $announcement = $form->getData();
            $announcement->setPostedBy($tokenStorage->getToken()->getUser()->getId());
            $announcementRepository->newAnnouncement($announcement);
            return $this->redirectToRoute('luxo_myannouncement_show');
        }

        return $this->render('newannouncement.html.twig',[
            'form' => $form->createView()
        ]);

    }
}
