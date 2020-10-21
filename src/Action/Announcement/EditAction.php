<?php


namespace Luxo\Action\Announcement;


use Luxo\Action\Action;
use Luxo\Entity\Image;
use Luxo\Form\AddAnnouncement;
use Luxo\Form\AddAnnouncementForm;
use Luxo\Repository\AnnouncementRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormFactory;


use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class EditAction extends Action
{
    /**
     * @Route(path="/user/announcement/{id}")
     * @param $id
     * @param AnnouncementRepository $announcementRepository
     * @param FormFactory $formFactory
     *
     * @param RequestStack $requestStack
     * @param TokenStorage $tokenStorage
     * @return Response
     */
    public function __invoke($id , AnnouncementRepository $announcementRepository, FormFactory $formFactory,RequestStack $requestStack, TokenStorage $tokenStorage)
    {
        if(!$tokenStorage->getToken()) {
            throw new Exception("Veuillez vous connecter");
        }
        $announcement =$announcementRepository->find($id);


        $form = $formFactory->createBuilder(AddAnnouncementForm::class, $announcementRepository->find($id))
            ->getForm();
        $form->handleRequest($requestStack->getCurrentRequest());

        if($form->getData()->getPostedBy()->getId() != $tokenStorage->getToken()->getUser()->getId()){
            throw new Exception("l'annonce n'a pas le votre");
        }



        if($form->isSubmitted() && $form->isValid()){

            $announcementRepository->edit($form->getData());
            return $this->redirectToRoute('luxo_announcement_list');
        }

        return $this->render('Announcement/Edit.html.twig',[
            'form' => $form->createView(),
            'images' => $announcement->getImages(),
        ]);
    }
}
