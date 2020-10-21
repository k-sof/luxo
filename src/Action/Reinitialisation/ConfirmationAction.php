<?php

namespace Luxo\Action\Reinitialisation;

use Luxo\Action\Action;
use Luxo\Form\DemandeForm;
use Luxo\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ConfirmationAction extends Action
{
    /**
     * @Route(path = "/confirmation")
     *
     * @param UserRepository $userRepository
     * @param RequestStack   $request
     * @param Mailer         $mailer
     * @param FormFactory    $formFactory
     *
     * @return Response
     */
    public function __invoke(UserRepository $userRepository, RequestStack $request, Mailer $mailer, FormFactory $formFactory)
    {
        $form = $formFactory->createBuilder(DemandeForm::class )
            ->getForm();

        $form->handleRequest($request->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findBy(['email' => $form->getData()->getEmail()]);
            if (!$user) {
                $form->addError(new FormError('email invalid'));
            } else {
                $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

                $user[0]->setToken($token);
                $userRepository->updateUser($user[0]);

                $mail = (new Email())
                        ->from('noreply@luxo.fr')
                        ->to(new Address($user[0]->getEmail()))
                        ->subject('Réinitialisation compte Luxo !')
                        ->html($this->render('Mail/emailReinit.html.twig', ['user' => $user[0]])->getContent());
                $mailer->send($mail);

                return $this->redirectToRoute('luxo_security_login');
            }
        }

        return $this->render('Reinitialisation/Confirmation.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
