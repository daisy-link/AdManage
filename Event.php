<?php

namespace Plugin\AdManage;

use Eccube\Application;
use Plugin\AdManage\Entity\Conversion;

class Event
{

    protected $app;
    protected $sessionPreOrderKey = 'eccube.plugin.ad_manage.preorderid';

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function onAppBefore()
    {
        $this->app['twig']->addGlobal('AdManage', true);
    }

    public function onShoppingCompleteBefore()
    {
        $app = $this->app;
        $preOrderId = $app['session']->get($this->sessionPreOrderKey);
        $app['session']->remove($this->sessionPreOrderKey);
        if(is_string($preOrderId) && strlen($preOrderId)){
            /** @var \Eccube\Entity\Order $Order */
            $conditions = array('pre_order_id' => $preOrderId, 'OrderStatus' => $app['config']['order_new']);
            $Order = $app['eccube.repository.order']->findOneBy($conditions);
            $uniqueId = $app['eccube.plugin.ad_manage.service.ad']->getUniqueId();
            if ($Order && is_string($uniqueId)) {
                $Conversion = new Conversion();
                $Conversion
                    ->setOrder($Order)
                    ->setUniqueId($uniqueId);
                $app['orm.em']->persist($Conversion);
                $app['orm.em']->flush();
                $app['eccube.plugin.ad_manage.service.ad']->clear();
            }
        }
    }

    public function onShoppingConfirmBefore()
    {
        $app = $this->app;
        $preOrderId = $app['eccube.service.cart']->getPreOrderId();
        $app['session']->set($this->sessionPreOrderKey, $preOrderId);
    }
}