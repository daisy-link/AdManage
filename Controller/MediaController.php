<?php

namespace Plugin\AdManage\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;

class MediaController
{
    public function index(Application $app, Request $request)
    {
        $builder = $app['form.factory']
            ->createBuilder('admin_media');
        $form = $builder->getForm();

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);
            if ($form->isValid()) {

                $Media = $form->getData();
                $Media->setDelFlg(0);

                try {
                    $app['orm.em']->persist($Media);
                    $app['orm.em']->flush($Media);
                    $app->addSuccess('admin.ad_manage.media.save.success', 'admin');
                } catch (\Exception $e) {
                    $app->addError('admin.ad_manage.media.save.failure', 'admin');
                }

                return $app->redirect($app->url('admin_media'));
            }
        }

        $Medium = $app['eccube.plugin.ad_manage.repository.media']->findBy(array(), array('id' => 'ASC'));

        return $app->renderView(
            'AdManage/View/admin/Media/index.twig',
            array(
                'form' => $form->createView(),
                'Medium' => $Medium,
            )
        );
    }

    public function delete(Application $app, $id)
    {
        $Media = $app['eccube.plugin.ad_manage.repository.media']->find($id);
        if (!$Media) {
            throw new NotFoundHttpException();
        }

        if ($Media instanceof \Plugin\AdManage\Entity\Media) {
            $Media->setDelFlg(1);
            $app['orm.em']->persist($Media);
            $app['orm.em']->flush();
            $app->addSuccess('admin.ad_manage.media.delete.success', 'admin');
        } else {
            $app->addError('admin.ad_manage.media.delete.failure', 'admin');
        }

        return $app->redirect($app->url('admin_media'));
    }
}