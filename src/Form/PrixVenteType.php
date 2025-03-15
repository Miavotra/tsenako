<?php

namespace App\Form;

use App\Entity\PrixVente;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrixVenteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('valeur') 
            ->add('Save', SubmitType::Class,[
                'label' => 'Enregistrer'
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, $this->updateDate(...));
        ;
    }
    public function updateDate(PostSubmitEvent $event) {
        $data = $event->getData();
        if(!($data instanceof PrixVente)) {
            return;
        }
        if(!($data->getID())){
            $data->setCreatedAt(new \DateTimeImmutable()); 
        }
    }
 
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PrixVente::class,
            'csrf_protection' => true, // ⚠️ Juste pour tester !

        ]);
    }
}
