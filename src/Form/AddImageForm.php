<?php


namespace Luxo\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddImageForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('path')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // ajouter le mapping du formulaire avec l'entity \Luxo\Entity\USer
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }

}
