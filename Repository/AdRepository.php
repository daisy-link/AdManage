<?php

namespace Plugin\AdManage\Repository;

use Doctrine\ORM\EntityRepository;

class AdRepository extends EntityRepository
{
    /**
     * 広告媒体を保存する。
     *
     * @param \Plugin\AdManage\Entity\Ad $Ad
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function save(\Plugin\AdManage\Entity\Ad $Ad)
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();

        try {
            if (!$Ad->getId()) {
                $Ad->setDelFlg(0);
            }

            $em->persist($Ad);
            $em->flush();
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();

            return false;
        }

        return true;
    }

    /**
     * 媒体コードの重複チェック。
     *
     * @param \Plugin\AdManage\Entity\Ad $Ad
     * @return bool 重複する場合、true
     */
    public function checkCodeDuplication(\Plugin\AdManage\Entity\Ad $Ad)
    {
        /** @var $softDeleteFilter \Eccube\Doctrine\Filter\SoftDeleteFilter */
        $softDeleteFilter = $this->getEntityManager()->getFilters()->getFilter('soft_delete');
        $originalExcludes = $softDeleteFilter->getExcludes();
        $softDeleteFilter->setExcludes(array('Plugin\AdManage\Entity\Ad'));

        $qb = $this->createQueryBuilder('ad');
        $qb->where('ad.code = :code')->setParameter('code', $Ad->getCode());

        if (is_numeric($Ad->getId())) {
            $qb->andWhere('ad.id != :id')->setParameter('id', $Ad->getId());
        }

        $result = $qb->getQuery()->getResult();
        $softDeleteFilter->setExcludes($originalExcludes);

        return !empty($result);
    }

    /**
     * findByの結果を媒体グループ別にネストして取得する。
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array The objects.
     */
    public function findNestedBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $Ads = $this->findBy($criteria, $orderBy, $limit, $offset);
        $results = array();
        
        foreach ($Ads as $Ad) {
            $mediaId = $Ad->getMedia()->getId();
            $results[$mediaId] = isset($results[$mediaId]) ? $results[$mediaId] : array();
            $results[$mediaId][] = $Ad;
        }
        
        return $results;
    }
}