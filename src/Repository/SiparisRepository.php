<?php

namespace App\Repository;

use App\Entity\Siparis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Siparis|null find($id, $lockMode = null, $lockVersion = null)
 * @method Siparis|null findOneBy(array $criteria, array $orderBy = null)
 * @method Siparis[]    findAll()
 * @method Siparis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiparisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Siparis::class);
    }

    // /**
    //  * @return Siparis[] Returns an array of Siparis objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Siparis
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function getAllSiparis():array
    {
        $conn= $this->getEntityManager()->getConnection();
        $sql='
                SELECT s.*,c.title as catname,u.name,u.surname FROM siparis s 
                JOIN category c ON c.id= s.category_id 
                JOIN user u ON u.id= s.userid 

                ORDER BY c.title DESC
        ';
        $smtp=$conn->prepare($sql);
        $smtp->execute();
        return $smtp->fetchAll();


    }
}
