<?php


namespace Luxo\Action\Compte;


use Doctrine\ORM\EntityManager;
use Luxo\Action\Action;
use Luxo\Form\CompteForm;
use Luxo\Repository\UserRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ShowAction extends Action
{
    /**
     * @Route(path="/user/compte")
     * @param FormFactory $formFactory
     * @param TokenStorage $tokenStorage
     * @param RequestStack $requestStack
     * @param UserRepository $userRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(FormFactory $formFactory, TokenStorage $tokenStorage, RequestStack $requestStack, UserRepository $userRepository)
    {
        if(!$tokenStorage->getToken()) {
            throw new Exception("Veuillez vous connecter");
        }
        $form = $formFactory->createBuilder(CompteForm::class, $tokenStorage->getToken()->getUser())
            ->getForm();
        $form->handleRequest($requestStack->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->updateUser($form->getData());
            return $this->redirectToRoute('luxo_compte_show');
        }

        return $this->render('compte.html.twig', [
           'form' => $form->createView(),
        ]);
    }
}
