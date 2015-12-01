<?php

namespace Plugin\AdManage\ServiceProvider;

use Eccube\Application;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
            $this->initService($app);
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
        $app->after(function (Request $request, Response $response, \Silex\Application $app) {
            $Plugin = $app['eccube.repository.plugin']->findOneBy(array('code' => 'AdManage', 'enable' => '1'));
            if(!empty($Plugin)) {
                $app['eccube.plugin.ad_manage.service.ad']->track($response);
            }
        });
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
        $app->match('/admin/ad_manage/media', '\Plugin\AdManage\Controller\MediaController::index')
            ->bind('admin_media');
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
                    $types[] = new \Plugin\AdManage\Form\Type\Admin\AdTotalType();
                    $types[] = new \Plugin\AdManage\Form\Type\Admin\MediaType();
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

                    $ymlPath = __DIR__ . '/../config';

                    $configAll['nav'] = array_map(
                        function ($nav) {
                            if ($nav['id'] == 'content') {
                                $nav['child'][] = array(
                                    'id' => 'media_master',
                                    'name' => '媒体グループ管理',
                                    'url' => 'admin_media',
                                );
                                $nav['child'][] = array(
                                    'id' => 'ad_master',
                                    'name' => '媒体管理',
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

                    $config = array();
                    $configYml = $ymlPath . '/config.yml';

                    if (file_exists($configYml)) {
                        $config = Yaml::parse($configYml);
                    }
                    $configAll = array_replace_recursive($configAll, $config);

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
        $app['eccube.plugin.ad_manage.repository.access'] = $app->share(
            function () use ($app) {
                $repository = $app['orm.em']->getRepository('Plugin\AdManage\Entity\Access');
                $repository->setApp($app);
                return $repository;
            }
        );
        $app['eccube.plugin.ad_manage.repository.conversion'] = $app->share(
            function () use ($app) {
                return $app['orm.em']->getRepository('Plugin\AdManage\Entity\Conversion');
            }
        );
        $app['eccube.plugin.ad_manage.repository.master.media'] = $app->share(
            function () use ($app) {
                return $app['orm.em']->getRepository('Plugin\AdManage\Entity\Master\Media');
            }
        );
    }

    public function initService(BaseApplication $app)
    {
        $app['eccube.plugin.ad_manage.service.ad'] = $app->share(function () use ($app) {
            $Plugin = $app['eccube.repository.plugin']->findOneBy(array('code' => 'AdManage', 'enable' => '1'));
            if(!empty($Plugin)){
                return new \Plugin\AdManage\Service\AdService($app);
            }
            else{
                return null;
            }
        });
    }

    public function boot(BaseApplication $app)
    {
    }
}