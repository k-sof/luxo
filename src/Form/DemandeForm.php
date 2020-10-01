<?php


namespace Luxo\Form;


use Luxo\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email'
            ]);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        // ajouter le mapping du formulaire avec l'entity \Luxo\Entity\USer
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
