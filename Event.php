<?php

namespace Plugin\AdManage;

use Eccube\Application;

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
}