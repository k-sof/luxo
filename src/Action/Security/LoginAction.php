<?php

namespace Luxo\Action\Security;

use Luxo\Action\Action;
use Luxo\Entity\User;
use Luxo\Form\LoginForm;
use Luxo\Repository\AnnouncementRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class LoginAction extends Action
{
    /**
     * @Route(path="/login")
     *
     * @param FormFactory $formFactory
     * @param RequestStack $requestStack
     * @param AuthenticationProviderManager $authentication
     * @param Session $session
     *
     * @param AnnouncementRepository $announcementRepository
     * @return Response
     */
    public function __invoke(FormFactory $formFactory, RequestStack $requestStack, AuthenticationProviderManager $authentication, Session $session, AnnouncementRepository $announcementRepository)
    {
        $request = $requestStack->getCurrentRequest();

        $form = $formFactory
            ->createBuilder(LoginForm::class)
            ->getForm()
            ;

        $form->handleRequest($request);

        $request->get('password');

        if ($form->isSubmitted() && $form->isValid()) {
            $token = new UsernamePasswordToken(
                $form->getData()['email'],
                $form->getData()['password'],
                User::class,
            );

            try {
                $authentication->authenticate($token);
                return $this->redirectToRoute('luxo_home_show', [
                    'session' => $session->get('token')->getUser(),
                    'announcements_location' => $announcementRepository->findByLocationWithImages(),
                    'announcements_achat' => $announcementRepository->findByAchatWithImages(),
                ]);
            } catch (BadCredentialsException $badCredentialsException) {
                $form->addError(new FormError('email ou mot de passe invalid'));
            }
        }

        return $this->render('User/Login.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
