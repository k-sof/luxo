<?php


namespace Luxo\Form;


use Luxo\Entity\Announcement;
use Luxo\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddAnnouncementForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre'
            ])
            ->add('description', TextareaType::class)
            ->add('city', TextType::class, [
                'label' => 'Ville'
            ])
            ->add('zipCode', IntegerType::class, [
                'label' => 'Postal'
            ])
            ->add('type', ChoiceType::class,[
                'choices' => array_flip(array_map('ucfirst',Announcement::TYPES)),
                'label' => 'Type de bien',
            ])
            ->add('price', TextType::class,[
                'label' => 'Prix',
            ])
            ->add('category',ChoiceType::class, [
                'choices' => array_flip(array_map('ucfirst',Announcement::CATEGORIES)),
                'label' => 'Catégorie',
            ])
            ->add('area', IntegerType::class, [
                'label' => 'Surface'
            ])
            ->add('room', TextType::class, [
                'label' => 'Pièce'
            ])
            ->add('bedroom',TextType::class, [
                'label' => 'Chambre'
            ])
            ->add('energy', ChoiceType::class, [
                'choices' => array_flip(array_map('ucfirst',Announcement::ENERGIES))
            ])
            ->add('floor', IntegerType::class, [
                'label' => 'Etage',
                'attr' =>[ 'min' => 0, 'max' => 20]
            ])
            ->add('sold', CheckboxType::class, [
                'required'   => false,
                'label' => 'Vendu',
                'attr' => ['class' => 'sold'],
            ])
            ->add('images' , CollectionType::class, [
                'entry_type' => AddImageForm::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'attr' =>['class' => 'form_collection']
            ])
            ->add('submit',SubmitType::class);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Announcement::class,

        ]);
    }

}
