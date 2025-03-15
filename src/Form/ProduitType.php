<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\PrixVente;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name') 
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'nom',
            ]) 
            ->add('Prix', TextType::Class, [
                'mapped' => false,
                'label' => 'prix'
            ])
            ->add('Save', SubmitType::Class,[
                'label' => 'Enregistrer'
            ])
            // ->addEventListener(FormEvents::POST_SUBMIT, $this->updateDate(...));
        ;
    }

    public function updateDate(PostSubmitEvent $event) {
        $data = $event->getData();
        if(!($data instanceof Produit)) {
            return;
        }
        if(!($data->getID())){
            $data->setCreatedAt(new \DateTimeImmutable()); 
        }
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true, // ⚠️ Juste pour tester !
            'data_class' => Produit::class,
        ]);
    }
}
