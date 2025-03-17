<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\PrixVente;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;


class ProduitType extends AbstractType
{

    // Injection de l'EntityManager
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        // Récupérer l'entité liée au formulaire
        $produit = $options['data'];

        // Récupérer une valeur dynamique depuis la base de données
        $valeur = $this->entityManager->getRepository(PrixVente::class)
            ->createQueryBuilder('pv')
            ->select('pv.valeur')
            ->where('pv.status = :active')
            ->andWhere('pv.produit = :produit')
            ->setParameter('active', 1)
            ->setParameter('produit', $produit->getId())
            ->setMaxResults(1) // Vous pouvez récupérer un seul produit, par exemple
            ->getQuery()
            ->getOneOrNullResult();

        $builder
            ->add('name')  
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'nom',
            ]) 
            ->add('Prix', TextType::Class, [
                'mapped' => false,
                'label' => 'prix',
                'data' => $valeur ? $valeur['valeur'] : '', // Texte par défaut ou récupéré
            ])
            ->add('Save', SubmitType::Class,[
                'label' => 'Enregistrer'
            ]);
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
            'data_class' => Produit::class,
            'csrf_protection' => true, // Assure-toi que c'est activé
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'produit_item', // Un identifiant unique
        ]);
    }
}
