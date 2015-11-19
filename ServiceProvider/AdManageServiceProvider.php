<?php

namespace Plugin\AdManage\ServiceProvider;

use Eccube\Application;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;

class AdManageServiceProvider implements ServiceProviderInterface
{

    public function register(BaseApplication $app)
    {
        $Plugin = $app['eccube.repository.plugin']->findOneBy(array('code' => 'AdManage', 'enable' => '1'));
        if (!empty($Plugin)) {
            $this->initConfig($app);
            $this->initRoute($app);
            $this->initForm($app);
            $this->initDoctrine($app);
            $this->initTranslator($app);
            $this->initPluginEventDispatcher($app);
        }
    }

    public function initTranslator(BaseApplication $app)
    {
        $app['translator'] = $app->share(
            $app->extend(
                'translator',
                function ($translator, \Silex\Application $app) {
                    $translator->addLoader('yaml', new \Symfony\Component\Translation\Loader\YamlFileLoader());

                    $file = __DIR__ . '/../Resource/locale/message.' . $app['locale'] . '.yml';
                    if (file_exists($file)) {
                        $translator->addResource('yaml', $file, $app['locale']);
                    }

                    return $translator;
                }
            )
        );
    }

    public function initPluginEventDispatcher(BaseApplication $app)
    {
//        $app->on(
//            \Symfony\Component\HttpKernel\KernelEvents::RESPONSE,
//            function (\Symfony\Component\HttpKernel\Event\FilterResponseEvent $event) use ($app) {
//                $route = $event->getRequest()->attributes->get('_route');
//                $app['eccube.event.dispatcher']->dispatch(
//                    'eccube.plugin.add_product_columns.event.render.' . $route . '.before',
//                    $event
//                );
//            }
//        );
    }

    public function initRoute(BaseApplication $app)
    {
        $app->match('/admin/ad_manage', '\Plugin\AdManage\Controller\AdController::index')
            ->bind('admin_ad');
        $app->match('/admin/ad_manage/{id}', '\Plugin\AdManage\Controller\AdController::index')
            ->assert('id', '^\d+$')
            ->bind('admin_ad_edit');
        $app->match('/admin/ad_manage/{id}/delete', '\Plugin\AdManage\Controller\AdController::delete')
            ->assert('id', '^\d+$')
            ->bind('admin_ad_delete');
        $app->match('/admin/ad_manage/total', '\Plugin\AdManage\Controller\AdController::total')
            ->bind('admin_ad_total');
    }

    public function initForm(BaseApplication $app)
    {
        $app['form.types'] = $app->share(
            $app->extend(
                'form.types',
                function ($types) use ($app) {
                    $types[] = new \Plugin\AdManage\Form\Type\Admin\AdType($app);
                    $types[] = new \Plugin\AdManage\Form\Type\Master\MediaType($app);

                    return $types;
                }
            )
        );
    }

    public function initConfig(BaseApplication $app)
    {
        $app['config'] = $app->share(
            $app->extend(
                'config',
                function ($configAll) {

//                    $ymlPath = __DIR__ . '/../config';

                    $configAll['nav'] = array_map(
                        function ($nav) {
                            if ($nav['id'] == 'content') {
                                $nav['child'][] = array(
                                    'id' => 'ad_master',
                                    'name' => '広告媒体管理',
                                    'url' => 'admin_ad',
                                );
                                $nav['child'][] = array(
                                    'id' => 'ad_total',
                                    'name' => '広告効果測定',
                                    'url' => 'admin_ad_total',
                                );
                            }

                            return $nav;
                        },
                        $configAll['nav']
                    );

//                    $config = array();
//                    $configYml = $ymlPath . '/config.yml';
//
//                    if (file_exists($configYml)) {
//                        $config = Yaml::parse($configYml);
//                    }
//                    $configAll = array_replace_recursive($configAll, $config);
//
                    return $configAll;
                }
            )
        );
    }

    public function initDoctrine(BaseApplication $app)
    {
        $app['eccube.plugin.ad_manage.repository.ad'] = $app->share(
            function () use ($app) {
                return $app['orm.em']->getRepository('Plugin\AdManage\Entity\Ad');
            }
        );
    }

    public function boot(BaseApplication $app)
    {
    }
}