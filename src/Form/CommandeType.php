<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\FormEvents;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description')
            ->add('quantite')
            ->add('prixUnitaire')
            ->add('prixTotal')
            ->add('reference')  
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'name', // Le champ à afficher dans le select
            ])
            ->add('User', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ])
            ->add('status', ChoiceType::class, [
                'choices'  => [
                    'En attente' => 'En attente' ,
                    'En cours' => 'En cours',
                    'Succées' => 'Succées' ,
                ]])
            ->add('Save', SubmitType::Class,[
                'label' => 'Enregistrer'
            ]) 
            ->addEventListener(FormEvents::POST_SUBMIT, $this->updateDate(...));
        ;
    }

    public function updateDate(PostSubmitEvent $event) {
        $data = $event->getData();
        if(!($data instanceof Commande)) {
            return;
        }
        if(!($data->getID())){
            $data->setReference(""); 
        }
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
            'csrf_protection' => true, // Assure-toi que c'est activé
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'commande_item', // Un identifiant unique
        ]);
    }
}
