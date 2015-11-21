<?php

namespace Plugin\AdManage;

use Eccube\Application;
use Plugin\AdManage\Entity\Conversion;

class Event
{

    protected $app;

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
        $orderId = $app['session']->get('eccube.front.shopping.order.id');
        if (is_numeric($orderId)) {
            /** @var \Eccube\Entity\Order $Order */
            $Order = $app['eccube.repository.order']->find($orderId);
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
}