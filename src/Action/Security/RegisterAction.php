<?php

namespace Luxo\Action\Security;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Luxo\Action\Action;
use Luxo\Form\RegisterForm;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class RegisterAction extends Action
{
    /**
     * @Route(path="/register")
     *
     * @param FormFactory   $formFactory
     * @param RequestStack  $request
     * @param EntityManager $em
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @return Response
     */
    public function __invoke(FormFactory $formFactory, RequestStack $request, EntityManager $em, Mailer $mailer)
    {
        $form = $formFactory
            ->createBuilder(RegisterForm::class)
            ->getForm()
        ;
        $form->handleRequest($request->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $email = (new Email())
                ->from('noreply@luxo.fr')
                ->to($form->getData()->getEmail())
                ->subject('Comfirmation de crée de compte Luxo !')
                ->html("
                    <h1>Luxo</h1>
                    <h3>Cher'.$form->getData()->getLastName() $form->getData()->getFirstName() ,</h3>
                    <p>Votre demande d\'ouverture de compte est <strong>terminée</strong>.</p>
                    <p>Toute l'équipe de luxo vous remercie de votre confiance et se réjouit de bientôt vous compter parmi ses nouveaux membre !</p>
                    ");
            $mailer->send($email);

            $em->persist($form->getData());
            $em->flush();
            $this->redirectToRoute('luxo_security_login');

           }

        return $this->render('register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
