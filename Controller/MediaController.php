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
                $result = $app['eccube.plugin.ad_manage.repository.master.media']->save($Media);

                if ($result) {

                    $app->addSuccess('admin.ad_manage.media.save.success', 'admin');

                    return $app->redirect($app->url('admin_media'));
                } else {

                    $app->addError('admin.ad_manage.media.save.failure', 'admin');
                }
            }
        }

        $Medium = $app['eccube.plugin.ad_manage.repository.master.media']->findBy(array(), array('rank' => 'ASC'));

        return $app->renderView(
            'AdManage/View/admin/Media/index.twig',
            array(
                'form' => $form->createView(),
                'Medium' => $Medium,
            )
        );
    }
}