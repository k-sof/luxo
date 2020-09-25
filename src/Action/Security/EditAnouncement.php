<?php


namespace Luxo\Action\Security;


use Luxo\Action\Action;
use Luxo\Form\AddAnnouncement;
use Symfony\Component\Form\FormFactory;

class EditAnouncement extends Action
{
    public function __invoke(FormFactory $formFactory)
    {
        $form = $formFactory->createBuilder(AddAnnouncement::class);
    }
}
