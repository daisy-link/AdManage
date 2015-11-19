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
            if(empty($EditAd)){
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

                    $app->addSuccess('admin.admanage.ad.save.success', 'admin');

                    return $app->redirect($app->url('admin_ad'));
                } else {

                    $app->addError('admin.admanage.ad.save.failure', 'admin');
                }
            }
        }

        $Ads = $app['eccube.plugin.ad_manage.repository.ad']->findBy(
            array(),
            array('id' => 'DESC')
        );

        return $app->renderView(
            'AdManage/View/admin/Ad/index.twig',
            array(
                'form' => $form->createView(),
                'Ads' => $Ads,
                'EditAd' => $EditAd,
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
            $app->addSuccess('admin.admanage.ad.delete.success', 'admin');
        } else {
            $app->addError('admin.admanage.ad.delete.failure', 'admin');
        }

        return $app->redirect($app->url('admin_ad'));
    }
}