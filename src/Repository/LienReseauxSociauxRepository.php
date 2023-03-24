<?php

namespace App\Repository;

use App\Entity\LienReseauxSociaux;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LienReseauxSociaux>
 *
 * @method LienReseauxSociaux|null find($id, $lockMode = null, $lockVersion = null)
 * @method LienReseauxSociaux|null findOneBy(array $criteria, array $orderBy = null)
 * @method LienReseauxSociaux[]    findAll()
 * @method LienReseauxSociaux[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LienReseauxSociauxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LienReseauxSociaux::class);
    }

    public function save(LienReseauxSociaux $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LienReseauxSociaux $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return LienReseauxSociaux[] Returns an array of LienReseauxSociaux objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?LienReseauxSociaux
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
