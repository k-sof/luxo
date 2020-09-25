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
    public function __invoke(FormFactory $formFactory, RequestStack $request, EntityManager $em)
    {
        $form = $formFactory
            ->createBuilder(RegisterForm::class)
            ->getForm()
        ;
        $form->handleRequest($request->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($form->getData());
            $em->flush();
            $this->redirectToRoute('luxo_security_login');
        }

        return $this->render('register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
