<?php

namespace Plugin\AdManage\Repository\Master;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Plugin\AdManage\Entity\Master\Media;

class MediaRepository extends EntityRepository
{
    /**
     * id => nameで取得する。
     * 
     * @return array
     */
    public function getList()
    {
        $result = $this->getEntityManager()
            ->createQuery('SELECT m.id, m.name FROM Plugin\AdManage\Entity\Master\Media m INDEX BY m.id')
            ->getResult();
        return array_map(function ($value) {
            return $value['name'];
        }, $result);
    }
    /**
     * 広告媒体グループを保存する。
     * 
     * @param \Plugin\AdManage\Entity\Master\Media $Media
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function save(\Plugin\AdManage\Entity\Master\Media $Media)
    {
        $em = $this->getEntityManager();
        $currentId = $this->createQueryBuilder('m')
            ->select('MAX(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
        if(!is_numeric($currentId)){
            $currentId = 0;
        }
        
        $currentRank = $this->createQueryBuilder('m')
            ->select('MAX(m.rank)')
            ->getQuery()
            ->getSingleScalarResult();
        if(!is_numeric($currentRank)){
            $currentRank = 0;
        }
        
        $Media
            ->setId($currentId + 1)
            ->setRank($currentRank + 1);

        try {
            $em->persist($Media);
            $em->flush();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}