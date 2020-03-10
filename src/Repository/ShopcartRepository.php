<?php

namespace App\Repository;

use App\Entity\Shopcart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Shopcart|null find($id, $lockMode = null, $lockVersion = null)
 * @method Shopcart|null findOneBy(array $criteria, array $orderBy = null)
 * @method Shopcart[]    findAll()
 * @method Shopcart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopcartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shopcart::class);
    }

    // /**
    //  * @return Shopcart[] Returns an array of Shopcart objects
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
    public function findOneBySomeField($value): ?Shopcart
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function getAllShopcart():array
    {
        $conn= $this->getEntityManager()->getConnection();
        $sql='
                SELECT C.*,u.name,u.surname,s.title FROM shopcart c
                    JOIN user u ON u.id= c.userid
                    JOIN siparis s ON s.id= c.siparisid
                    ORDER BY c.id DESC
        ';
        $smtp=$conn->prepare($sql);
        $smtp->execute();
        return $smtp->fetchAll();


    }
    public function getAllShopcartUser($userid):array
    {
        $qb=$this->createQueryBuilder('c')
            ->select('c.id,c.userid,c.siparisid,c.quantity,s.title')
            ->leftJoin('App\Entity\Siparis', 's','WITH','s.id=c.siparisid')
            ->where('c.userid = :userid')
            ->setParameter('userid',$userid)
            ->orderBy('c.id','DESC');
        $query=$qb->getQuery();
        return $query->execute();

    }
    public function getUserShopCartTotal($userid): float
    {
        $em = $this->getEntityManager();
        $qb=$this->createQueryBuilder('c')
                -> select( 'sum(s.price * c.quantity) as total')
                ->leftJoin('App\Entity\Siparis', 's','WITH ', 's.id=c.siparisid')
                ->leftJoin('App\Entity\Shopcart', 'c','WITH ', 's.id=c.siparisid')
            ->where('c.userid = :userid')
            ->setParameter('userid',$userid)
            ->orderBy('c.id','DESC');
        $result = $qb->getMaxResults();

        if ($result[0]["total"]!=null){
            return $result[0]["total"];
        }else{
            return 0;
        }


}
    public function getUserShopCartCount($userid): Integer
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('
        SELECT count(s.id) as shopcount
                FROM App\Entity\Shopcart s
                WHERE s.userid=:userid
        ')
            ->setParameter('userid', $userid);
        $result = $query->getResult();

        if ($result[0]["shopcount"]!=null){
            return $result[0]["shopcount"];
        }else{
            return 0;
        }
    }
  /*  public function getUserShopcart($userid): array
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('
        SELECT c.title, c.price, c.quantity, c.siparisid, c.userid, (s.sprice*c.quantity)as total
                FROM App\Entity\Shopcart c, App\Entity\Admin\Siparis s
                WHERE c.siparisid = s.id and c.userid=:userid
        ')
            ->setParameter('userid', $userid);
        return $query->getResult();
    }
  */
}
