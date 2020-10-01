<?php


namespace Luxo\Form;


use Luxo\Entity\Announcement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
                'choices' => $this->getType(),
                'label' => 'Type de bien',
            ])
            ->add('price', TextType::class,[
                'label' => 'Prix',
            ])
            ->add('category',ChoiceType::class, [
                'choices' => $this->getCat(),
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
                'choices' => $this->getEnergy()
            ])
            ->add('floor', IntegerType::class, [
                'label' => 'Etage'
            ])
            ->add('sold', CheckboxType::class, [
                'required'   => false,
                'label' => 'Vendu',
                'attr' => ['class' => 'sold'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Announcement::class,
        ]);
    }
    public function getType(){
        $choice =[];
        foreach (Announcement::TYPE_AD as $key => $value){
            $choice[$value] = $key;
        }
        return $choice;
    }
    public function getCat(){
        $choice =[];
        foreach (Announcement::CAT_AD as $key => $value){
            $choice[$value] = $key;
        }
        return $choice;
    }
    public function getEnergy(){
        $choice = [];
        foreach (Announcement::ENERGY_AD as $key => $value){
            $choice[$value]= $key;
        }
        return $choice;
    }
}
