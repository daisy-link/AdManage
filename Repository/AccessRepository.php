<?php

namespace Plugin\AdManage\Repository;

use Doctrine\ORM\EntityRepository;

class AccessRepository extends EntityRepository
{
    /** @var Application $app */
    protected $app;

    public function setApp(\Eccube\Application $app)
    {
        $this->app = $app;
    }
    
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

        foreach ($Accesses as $Access) {
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
        }
        $em->flush();
        $em->getConnection()->commit();
    }

    /**
     * 媒体グループごとのサマリーを取得する。
     *
     * @param array $searchData
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getMediaSummary($searchData)
    {
        $where = '1 = 1';
        $orderWhere = '1 = 1';
        $eb = $this->getEntityManager()->getExpressionBuilder();
        
        if(!empty($searchData['total_date_from']) && $searchData['total_date_from']){
            $where .= ' AND ac.create_date >= :totalDateFrom';
            $totalDateFrom = $searchData['total_date_from']
                ->format('Y-m-d H:i:s');
        }
        
        if(!empty($searchData['total_date_to']) && $searchData['total_date_to']){
            $where .= ' AND ac.create_date <= :totalDateTo';
            $totalDateTo = $searchData['total_date_to']
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
        }
        
        if(!empty($searchData['order_status']) && count($searchData['order_status'])){
            $orderStatuses = array();
            foreach($searchData['order_status'] as $orderStatus){
                $orderStatuses[] = $orderStatus->getId();
            }
            $orderWhere .= sprintf(' AND %s', $eb->in('status', $orderStatuses));
        }
        
        $sql = <<<EOSQL
SELECT
    *,
    m.media_id AS media_id,
    CASE
        WHEN unique_user_count > 0 THEN conversion_count / CAST(unique_user_count AS DECIMAL)
        ELSE 0
    END AS conversion_rate,
    CASE
        WHEN conversion_count > 0 THEN payment_total / CAST(conversion_count AS DECIMAL)
        ELSE 0
    END AS payment_average,
    CASE
        WHEN unique_user_count > 0 THEN revisit_user_count / CAST(unique_user_count AS DECIMAL)
        ELSE 0
    END AS revisit_user_rate,
    CASE
        WHEN conversion_access_count > 0 THEN (payment_total / CAST(conversion_access_count AS DECIMAL)) * conversion_count_all
        ELSE 0
    END AS payment_contribution_total,
    CASE
        WHEN conversion_access_count > 0 THEN payment_total / CAST(conversion_access_count AS DECIMAL)
        ELSE 0
    END AS payment_contribution_average
FROM (
    SELECT media_id, name, del_flg FROM plg_dtb_media
    UNION ALL SELECT -2, '直接 (Direct)', 0
    UNION ALL SELECT -1, '間接 (Referrer)', 0
) m
    LEFT JOIN (
        SELECT
            ac.media_id,
            SUM(user_access_count) AS access_count,
            COUNT(DISTINCT unique_id) AS unique_user_count,
            COUNT(user_revisit_count > 0 OR NULL) AS revisit_user_count,
            SUM(user_conversion_access_count) AS conversion_access_count,
            SUM(conversion_count) AS conversion_count,
            SUM(user_conversion_count_all) AS conversion_count_all,
            SUM(user_payment_total) AS payment_total
        FROM (
            SELECT
                ac.media_id,
                ac.unique_id,
                COUNT(ac.access_id) AS user_access_count,
                MAX(user_conversion_access_count) AS user_conversion_access_count,
                COUNT(DISTINCT o.order_id) AS conversion_count,
                COUNT(o.order_id IS NOT NULL OR NULL) AS user_conversion_count_all,
                MAX(o.payment_total) AS user_payment_total,
                COUNT(is_revisit = 1 OR NULL) AS user_revisit_count
            FROM (
                SELECT
                    ac.*,
                    CASE
                        WHEN media_id IS NOT NULL THEN media_id
                        WHEN referrer IS NULL THEN -2
                        ELSE -1
                    END AS media_id,
                    CASE WHEN EXISTS (
                        SELECT *
                        FROM plg_dtb_access old_ac
                        WHERE ac.unique_id = old_ac.unique_id
                        AND ac.history < old_ac.history
                    ) THEN 1 ELSE 0 END AS is_revisit,
                    COALESCE((
                        SELECT COUNT(1)
                        FROM plg_dtb_access conversion_ac
                            INNER JOIN plg_dtb_conversion c
                                USING(unique_id)
                            INNER JOIN dtb_order o
                                USING(order_id)
                        WHERE ac.unique_id = conversion_ac.unique_id
                    ), 0) AS user_conversion_access_count
                FROM plg_dtb_access ac
                    LEFT JOIN plg_dtb_ad ad
                        ON ac.ad_code = ad.code
                        AND ad.del_flg = 0
                WHERE NOT (
                    ac.ad_code IS NOT NULL
                    AND media_id IS NULL
                )
            ) ac
                LEFT JOIN plg_dtb_conversion c
                    ON ac.unique_id = c.unique_id
                LEFT JOIN dtb_order o
                    ON c.order_id = o.order_id
                    AND o.del_flg = 0
                    AND $orderWhere
            WHERE $where
            GROUP BY ac.media_id, ac.unique_id
        ) ac
        GROUP BY ac.media_id
    ) ac
        ON m.media_id = ac.media_id
WHERE del_flg = 0
OR del_flg IS NULL
ORDER BY m.media_id ASC
EOSQL;
        $stmt = $this->getEntityManager()
            ->getConnection()
            ->prepare($sql);

        if(isset($totalDateFrom)){
            $stmt->bindParam(':totalDateFrom', $totalDateFrom);
        }
        
        if(isset($totalDateTo)){
            $stmt->bindParam(':totalDateTo', $totalDateTo);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * 媒体ごとのサマリーを取得する。
     *
     * @param array $searchData
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAdSummary($searchData)
    {
        $where = '1 = 1';
        $orderWhere = '1 = 1';
        $eb = $this->getEntityManager()->getExpressionBuilder();

        if(!empty($searchData['total_date_from']) && $searchData['total_date_from']){
            $where .= ' AND ac.create_date >= :totalDateFrom';
            $totalDateFrom = $searchData['total_date_from']
                ->format('Y-m-d H:i:s');
        }

        if(!empty($searchData['total_date_to']) && $searchData['total_date_to']){
            $where .= ' AND ac.create_date <= :totalDateTo';
            $totalDateTo = $searchData['total_date_to']
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
        }

        if(!empty($searchData['order_status']) && count($searchData['order_status'])){
            $orderStatuses = array();
            foreach($searchData['order_status'] as $orderStatus){
                $orderStatuses[] = $orderStatus->getId();
            }
            $orderWhere .= sprintf(' AND %s', $eb->in('status', $orderStatuses));
        }
        
        $sql = <<<EOSQL
SELECT
    *,
    CASE
        WHEN unique_user_count > 0 THEN conversion_count / CAST(unique_user_count AS DECIMAL)
        ELSE 0
    END AS conversion_rate,
    CASE
        WHEN conversion_count > 0 THEN payment_total / CAST(conversion_count AS DECIMAL)
        ELSE 0
    END AS payment_average,
    CASE
        WHEN unique_user_count > 0 THEN revisit_user_count / CAST(unique_user_count AS DECIMAL)
        ELSE 0
    END AS revisit_user_rate,
    CASE
        WHEN conversion_access_count > 0 THEN payment_total / CAST(conversion_access_count AS DECIMAL)
        ELSE 0
    END AS payment_contribution_average,
    CASE
        WHEN conversion_access_count > 0 THEN (payment_total / CAST(conversion_access_count AS DECIMAL)) * conversion_count_all
        ELSE 0
    END AS payment_contribution_total,
    CASE
        WHEN conversion_count_all > 0 THEN conversion_count_1 / CAST(conversion_count_all AS DECIMAL)
        ELSE NULL
    END AS direct_conversion_rate,
    CASE
        WHEN conversion_count_all > 0 THEN conversion_count_2_or_more / CAST(conversion_count_all AS DECIMAL)
        ELSE NULL
    END AS indirect_conversion_rate
FROM plg_dtb_ad ad
    LEFT JOIN (
        SELECT
            ac.ad_code,
            SUM(user_access_count) AS access_count,
            COUNT(DISTINCT unique_id) AS unique_user_count,
            COUNT(user_revisit_count > 0 OR NULL) AS revisit_user_count,
            SUM(user_conversion_access_count) AS conversion_access_count,
            SUM(conversion_count) AS conversion_count,
            SUM(user_conversion_count_all) AS conversion_count_all,
            SUM(user_conversion_count_1)  AS conversion_count_1,
            SUM(user_conversion_count_2)  AS conversion_count_2,
            SUM(user_conversion_count_3)  AS conversion_count_3,
            SUM(user_conversion_count_4)  AS conversion_count_4,
            SUM(user_conversion_count_2_or_more) AS conversion_count_2_or_more,
            SUM(user_conversion_count_5_or_more) AS conversion_count_5_or_more,
            SUM(user_payment_total) AS payment_total
        FROM (
            SELECT
                ac.ad_code,
                ac.unique_id,
                COUNT(ac.access_id) AS user_access_count,
                MAX(user_conversion_access_count) AS user_conversion_access_count,
                COUNT(DISTINCT o.order_id) AS conversion_count,
                COUNT(o.order_id IS NOT NULL OR NULL) AS user_conversion_count_all,
                COUNT(o.order_id IS NOT NULL AND ac.history = 1 OR NULL) AS user_conversion_count_1,
                COUNT(o.order_id IS NOT NULL AND ac.history = 2 OR NULL) AS user_conversion_count_2,
                COUNT(o.order_id IS NOT NULL AND ac.history = 3 OR NULL) AS user_conversion_count_3,
                COUNT(o.order_id IS NOT NULL AND ac.history = 4 OR NULL) AS user_conversion_count_4,
                COUNT(o.order_id IS NOT NULL AND ac.history > 1 OR NULL) AS user_conversion_count_2_or_more,
                COUNT(o.order_id IS NOT NULL AND ac.history > 4 OR NULL) AS user_conversion_count_5_or_more,
                MAX(o.payment_total) AS user_payment_total,
                COUNT(is_revisit = 1 OR NULL) AS user_revisit_count
            FROM (
                SELECT
                    ac.*,
                    CASE WHEN EXISTS (
                        SELECT *
                        FROM plg_dtb_access old_ac
                        WHERE ac.unique_id = old_ac.unique_id
                        AND ac.history < old_ac.history
                    ) THEN 1 ELSE 0 END AS is_revisit,
                    COALESCE((
                        SELECT COUNT(1)
                        FROM plg_dtb_access conversion_ac
                            INNER JOIN plg_dtb_conversion c
                                USING(unique_id)
                            INNER JOIN dtb_order o
                                USING(order_id)
                        WHERE ac.unique_id = conversion_ac.unique_id
                    ), 0) AS user_conversion_access_count
                FROM plg_dtb_access ac
            ) ac
                LEFT JOIN plg_dtb_conversion c
                    ON ac.unique_id = c.unique_id
                LEFT JOIN dtb_order o
                    ON c.order_id = o.order_id
                    AND $orderWhere
            WHERE $where
            GROUP BY ac.ad_code, ac.unique_id
        ) ac
        GROUP BY ac.ad_code
    ) ac
        ON ad.code = ac.ad_code
WHERE del_flg = 0
ORDER BY ad.media_id ASC, ad.ad_id ASC
EOSQL;
        $stmt = $this->getEntityManager()
            ->getConnection()
            ->prepare($sql);

        if(isset($totalDateFrom)){
            $stmt->bindParam(':totalDateFrom', $totalDateFrom);
        }

        if(isset($totalDateTo)){
            $stmt->bindParam(':totalDateTo', $totalDateTo);
        }
        
        $stmt->execute();
        $ads = $stmt->fetchAll();
        $medium = $this->app['eccube.plugin.ad_manage.repository.media']->getList();
        $results = array();
        
        foreach($ads as $ad){
            $mediaId = $ad['media_id'];
            if(isset($medium[$mediaId])) {
                $results[$mediaId] = isset($results[$mediaId]) ? $results[$mediaId] : array();
                $results[$mediaId][] = $ad;
            }
        }
        
        return $results;
    }
}