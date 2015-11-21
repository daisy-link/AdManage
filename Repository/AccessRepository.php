<?php

namespace Plugin\AdManage\Repository;

use Doctrine\ORM\EntityRepository;

class AccessRepository extends EntityRepository
{
    /**
     * アクセス回数を更新する。
     * FIXME MySQLのUPDATE時の自己結合問題のため、逐次UPDATEにしてある。
     * 
     * @param $uniqueId
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function updateHistory($uniqueId)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        $Accesses = $this->findBy(array('unique_id' => $uniqueId));
        
        foreach($Accesses as $Access){
            $history = $this->createQueryBuilder('ac')
                ->select('COUNT(history.id) + 1')
                ->join('Plugin\AdManage\Entity\Access', 'history')
                ->where('ac.id = :id')
                ->andWhere('ac.unique_id = history.unique_id')
                ->andWhere('ac.id < history.id')
                ->setParameter('id', $Access->getId())
                ->getQuery()
                ->getSingleScalarResult();
            $Access->setHistory($history);
            $em->persist($Access);
            $em->flush();
        }
        
        $em->getConnection()->commit();
    }
}