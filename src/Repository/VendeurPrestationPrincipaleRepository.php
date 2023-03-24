<?php

namespace App\Repository;

use App\Entity\VendeurPrestationPrincipale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendeurPrestationPrincipale>
 *
 * @method VendeurPrestationPrincipale|null find($id, $lockMode = null, $lockVersion = null)
 * @method VendeurPrestationPrincipale|null findOneBy(array $criteria, array $orderBy = null)
 * @method VendeurPrestationPrincipale[]    findAll()
 * @method VendeurPrestationPrincipale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VendeurPrestationPrincipaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendeurPrestationPrincipale::class);
    }

    public function save(VendeurPrestationPrincipale $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendeurPrestationPrincipale $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return VendeurPrestationPrincipale[] Returns an array of VendeurPrestationPrincipale objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?VendeurPrestationPrincipale
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
