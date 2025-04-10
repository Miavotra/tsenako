<?php

namespace App\Repository;

use App\Entity\CommandeProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommandeProduit>
 */
class CommandeProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandeProduit::class);
    }

    /**
    * @return CommandeProduit[] Returns an array of CommandeProduit objects
    */
    public function findByCommand($value): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.commande = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get all products with 'done' status and sum the quantity for each product.
     *
     * @return array
     */
    public function findProductByStatus($status): array
    { 
        return $this->createQueryBuilder('c')
        ->andWhere('c.status = :val')
        ->setParameter('val', $status)
        ->orderBy('c.Produit', 'ASC')
        ->getQuery()
        ->getResult()
        ;
    } 
    /**
     * Get all products with 'done' status and sum the quantity for each product.
     *
     * @return array
     */
    public function findProductSuivi(): array
    { 
        return $this->createQueryBuilder('c')
        ->andWhere('c.status = :val')
        ->setParameter('val', "Livrée")
        ->orderBy('c.updatedAt', 'DESC')
        ->getQuery()
        ->getResult()
        ;
    } 

    //    public function findOneBySomeField($value): ?CommandeProduit
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
