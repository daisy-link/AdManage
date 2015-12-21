<?php

namespace Plugin\AdManage\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class MediaRepository extends EntityRepository
{
    /**
     * media_id => nameで取得する。
     * 
     * @return array
     */
    public function getList()
    {
        $result = $this->getEntityManager()
            ->createQuery('SELECT m.id, m.name FROM Plugin\AdManage\Entity\Media m INDEX BY m.id')
            ->getResult();
        return array_map(function ($value) {
            return $value['name'];
        }, $result);
    }
}