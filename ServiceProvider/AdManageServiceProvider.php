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
            $this->initRendering($app);
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
//        $app->match('/admin/product/column', '\Plugin\AddProductColumns\Controller\ColumnController::index')
//            ->bind('admin_product_column');
//        $app->match('/admin/product/column/{id}', '\Plugin\AddProductColumns\Controller\ColumnController::index')
//            ->assert('id', '^\d+$')
//            ->bind('admin_product_column_edit');
//        $app->match('/admin/product/column/{id}/up', '\Plugin\AddProductColumns\Controller\ColumnController::up')
//            ->assert('id', '^\d+$')
//            ->bind('admin_product_column_up');
//        $app->match('/admin/product/column/{id}/down', '\Plugin\AddProductColumns\Controller\ColumnController::down')
//            ->assert('id', '^\d+$')
//            ->bind('admin_product_column_down');
//        $app->match('/admin/product/column/move', '\Plugin\AddProductColumns\Controller\ColumnController::move')
//            ->bind('admin_product_column_move');
//        $app->match(
//            '/admin/product/column/{id}/delete',
//            '\Plugin\AddProductColumns\Controller\ColumnController::delete'
//        )
//            ->bind('admin_product_column_delete');
//
//        $app->post('/admin/product/column/image', '\Plugin\AddProductColumns\Controller\ProductController::addImage')
//            ->bind('admin_product_column_image_add');
    }

    public function initForm(BaseApplication $app)
    {
//        $app['form.type.extensions'] = $app->share(
//            $app->extend(
//                'form.type.extensions',
//                function ($extensions) use ($app) {
//                    $extensions[] = new \Plugin\AddProductColumns\Form\Extension\ProductTypeExtension($app);
//
//                    return $extensions;
//                }
//            )
//        );
//
//        $app['form.types'] = $app->share(
//            $app->extend(
//                'form.types',
//                function ($types) use ($app) {
//                    $types[] = new \Plugin\AddProductColumns\Form\Type\Admin\ColumnType($app);
//                    $types[] = new \Plugin\AddProductColumns\Form\Type\Master\ColumnTypeType();
//
//                    return $types;
//                }
//            )
//        );
    }

    public function initConfig(BaseApplication $app)
    {
        $app['config'] = $app->share(
            $app->extend(
                'config',
                function ($configAll) {

                    $ymlPath = __DIR__ . '/../config';

//                    $configAll['nav'] = array_map(
//                        function ($nav) {
//                            if ($nav['id'] == 'product') {
//                                $nav['child'][] = array(
//                                    'id' => 'product_column',
//                                    'name' => '商品情報管理',
//                                    'url' => 'admin_product_column',
//                                );
//                            }
//
//                            return $nav;
//                        },
//                        $configAll['nav']
//                    );
//
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
//        $app['eccube.plugin.add_product_columns.repository.column'] = $app->share(
//            function () use ($app) {
//                return $app['orm.em']->getRepository('Plugin\AddProductColumns\Entity\Column');
//            }
//        );
//
//        $app['eccube.plugin.add_product_columns.repository.column_type'] = $app->share(
//            function () use ($app) {
//                return $app['orm.em']->getRepository('Plugin\AddProductColumns\Entity\Master\ColumnType');
//            }
//        );
//
//        $app['eccube.plugin.add_product_columns.repository.product_column'] = $app->share(
//            function () use ($app) {
//                return $app['orm.em']->getRepository('Plugin\AddProductColumns\Entity\ProductColumn');
//            }
//        );
    }

    public function initRendering(BaseApplication $app)
    {
//        $app['twig'] = $app->share(
//            $app->extend(
//                'twig',
//                function (\Twig_Environment $twig, \Silex\Application $app) {
//                    $twig->addExtension(new \Plugin\AddProductColumns\Twig\Extension\AddProductColumnsExtension($app));
//
//                    return $twig;
//                }
//            )
//        );
    }

    public function boot(BaseApplication $app)
    {
    }
}