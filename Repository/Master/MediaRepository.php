<?php

namespace Plugin\AdManage\Repository\Master;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

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
}