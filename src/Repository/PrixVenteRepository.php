<?php

namespace App\Repository;

use App\Entity\PrixVente;
use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrixVente>
 */
class PrixVenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrixVente::class);
    }

    public function setStatusToZero(Produit $produit, $value): void
    {
        // Créer un QueryBuilder pour effectuer l'update
        $qb = $this->createQueryBuilder('pv');

        $qb->update(PrixVente::class, 'pv')
            ->set('pv.status', ':status')
            ->where('pv.produit = :produit_id')
            ->setParameter('status', $value)
            ->setParameter('produit_id', $produit->getId());

        // Exécuter la requête
        $qb->getQuery()->execute();
    }

    //    /**
    //     * @return PrixVente[] Returns an array of PrixVente objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PrixVente
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
