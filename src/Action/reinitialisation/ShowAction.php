<?php


namespace Luxo\Action\reinitialisation;


use http\Env\Response;
use Luxo\Action\Action;
use Luxo\Form\ReinitialisationForm;
use Luxo\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class ShowAction extends Action
{
    /**
     * @Route(path="/reinitialisation/{token}{id}")
     * @param UserRepository $userRepository
     * @param email $email
     * @param token $token
     * @param id $id
     * @return Response
     */
    public function __invoke($token, $id, UserRepository $userRepository, FormFactory $formFactory, RequestStack $request)
    {
        $user = $userRepository->findBy(['id'=> $id])[0];

        if(!$user){
            $this->redirectToRoute('luxo_demande_show');
        }
        if($user->getToken() != $token){
            $this->redirectToRoute('luxo_demande_show');
        }

        $form =$formFactory->createBuilder(ReinitialisationForm::class)
            ->getForm();

        $form->handleRequest($request->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()){
            if($form->getData()->getPassword() != $form->getData()->getConfirmation()){
                $form->addError(new FormError('veuillez verifier votre nouveau mot de passe'));
            }
            $user->setPassword($form->getData()->getPassword());
            $user->setToken(null);
            $userRepository->updateUser($user);
            return $this->redirectToRoute('luxo_security_login');
        }

        return $this->render('reinitialisation.html.twig', [
            'form' => $form->createView()
        ]);

    }

}
