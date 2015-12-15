<?php

namespace Plugin\AdManage\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdController
{
    public function index(Application $app, Request $request, $id = null)
    {
        if (is_null($id)) {
            $EditAd = new \Plugin\AdManage\Entity\Ad();
        } else {
            $EditAd = $app['eccube.plugin.ad_manage.repository.ad']->find($id);
            if (empty($EditAd)) {
                throw new NotFoundHttpException();
            }
        }

        $builder = $app['form.factory']
            ->createBuilder('admin_ad', $EditAd);
        $form = $builder->getForm();

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);
            if ($form->isValid()) {

                $newAd = $form->getData();
                $result = $app['eccube.plugin.ad_manage.repository.ad']->save($newAd);

                if ($result) {

                    $app->addSuccess('admin.ad_manage.ad.save.success', 'admin');

                    return $app->redirect($app->url('admin_ad'));
                } else {

                    $app->addError('admin.ad_manage.ad.save.failure', 'admin');
                }
            }
        }

        $Ads = $app['eccube.plugin.ad_manage.repository.ad']->findNestedBy(array(),
            array(
                'Media' => 'ASC',
                'id' => 'ASC',
            )
        );

        $medium = $app['eccube.plugin.ad_manage.repository.media']->getList();

        return $app->renderView(
            'AdManage/View/admin/Ad/index.twig',
            array(
                'form' => $form->createView(),
                'Ads' => $Ads,
                'EditAd' => $EditAd,
                'medium' => $medium,
                'url' => sprintf('http://%s%s?%s=', $_SERVER['SERVER_NAME'], $app['config']['root_urlpath'],
                    $app['config']['ad_code_url_key']),
            )
        );
    }

    public function delete(Application $app, $id)
    {
        $Ad = $app['eccube.plugin.ad_manage.repository.ad']->find($id);
        if (!$Ad) {
            throw new NotFoundHttpException();
        }

        if ($Ad instanceof \Plugin\AdManage\Entity\Ad) {
            $Ad->setDelFlg(1);
            $app['orm.em']->persist($Ad);
            $app['orm.em']->flush();
            $app->addSuccess('admin.ad_manage.ad.delete.success', 'admin');
        } else {
            $app->addError('admin.ad_manage.ad.delete.failure', 'admin');
        }

        return $app->redirect($app->url('admin_ad'));
    }

    public function total(Application $app, Request $request)
    {
        $form = $app['form.factory']
            ->createBuilder('admin_ad_total')
            ->getForm();

        $search = false;
        $medium = array();
        $mediaSummary = array();
        $adSummary = array();

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);
            if ($form->isValid()) {

                $search = true;
                $data = $form->getData();
                $medium = $app['eccube.plugin.ad_manage.repository.media']->getList();
                $mediaSummary = $app['eccube.plugin.ad_manage.repository.access']->getMediaSummary($data);
                $adSummary = $app['eccube.plugin.ad_manage.repository.access']->getAdSummary($data);
            }
        }

        return $app->renderView('AdManage/View/admin/Ad/total.twig',
            array(
                'mediaSummary' => $mediaSummary,
                'adSummary' => $adSummary,
                'medium' => $medium,
                'form' => $form->createView(),
                'search' => $search,
            )
        );
    }
}