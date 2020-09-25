<?php


namespace Luxo\Action\Edit;


use Luxo\Action\Action;
use Luxo\Form\AddAnnouncement;
use Luxo\Repository\AnnouncementRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormFactory;


use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ShowAction extends Action
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
        $form = $formFactory->createBuilder(AddAnnouncement::class, $announcementRepository->find($id))
            ->getForm();
        
        if($form->getData()->getPostedBy()->getId() != $tokenStorage->getToken()->getUser()->getId()){
            throw new Exception("l'annonce n'a pas le votre");
        }
        
        $form->handleRequest($requestStack->getCurrentRequest());

        if($form->isSubmitted() && $form->isValid()){
            
            $announcementRepository->edit($form->getData());
            return $this->redirectToRoute('luxo_myannouncement_show');
        }

        return $this->render('Edit.html.twig',[
            'form' => $form->createView()
        ]);
    }
}
