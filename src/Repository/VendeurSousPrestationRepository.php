<?php

namespace App\Repository;

use App\Entity\VendeurSousPrestation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VendeurSousPrestation>
 *
 * @method VendeurSousPrestation|null find($id, $lockMode = null, $lockVersion = null)
 * @method VendeurSousPrestation|null findOneBy(array $criteria, array $orderBy = null)
 * @method VendeurSousPrestation[]    findAll()
 * @method VendeurSousPrestation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VendeurSousPrestationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendeurSousPrestation::class);
    }

    public function save(VendeurSousPrestation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendeurSousPrestation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return VendeurSousPrestation[] Returns an array of VendeurSousPrestation objects
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

//    public function findOneBySomeField($value): ?VendeurSousPrestation
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
