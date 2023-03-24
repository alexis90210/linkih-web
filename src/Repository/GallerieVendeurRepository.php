<?php

namespace App\Repository;

use App\Entity\GallerieVendeur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GallerieVendeur>
 *
 * @method GallerieVendeur|null find($id, $lockMode = null, $lockVersion = null)
 * @method GallerieVendeur|null findOneBy(array $criteria, array $orderBy = null)
 * @method GallerieVendeur[]    findAll()
 * @method GallerieVendeur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GallerieVendeurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GallerieVendeur::class);
    }

    public function save(GallerieVendeur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GallerieVendeur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return GallerieVendeur[] Returns an array of GallerieVendeur objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?GallerieVendeur
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
