<?php

namespace Plugin\AdManage\Form\Type\Admin;

use Eccube\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AdType extends AbstractType
{

    protected $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_ad';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Plugin\AdManage\Entity\Ad',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $app = $this->app;
        $builder
            ->add(
                'id', 'hidden'
            )
            ->add(
                'name',
                'text',
                array(
                    'label' => '媒体名',
                    'constraints' => array(
                        new Assert\NotBlank(),
                    ),
                )
            )
            ->add(
                'Media',
                'media',
                array(
                    'constraints' => array(
                        new Assert\NotBlank(),
                    ),
                )
            )
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber())
            ->addEventListener(FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($app) {
                    $data = $event->getData();
                    $form = $event->getForm();
                    if(!$data || !$data->getId()){
                        $form->add('code', 'text', array(
                            'label' => '媒体コード',
                            'constraints' => array(
                                new Assert\NotBlank(),
                                new Assert\Regex(array(
                                    'pattern' => '/^[[:alnum:]-_.]+$/',
                                    'message' => 'form.type.ad_code.invalid',
                                )),
                            ),
                        ));
                    }
                    else{
                        $form->add('code', 'text', array(
                            'label' => '媒体コード',
                            'constraints' => array(
                                new Assert\NotBlank(),
                                new Assert\Regex(array(
                                    'pattern' => '/^[[:alnum:]-_.]+$/',
                                    'message' => 'form.type.ad_code.invalid',
                                )),
                            ),
                            'disabled' => true,
                        ));
                    }
                }
            )
            ->addEventListener(FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($app) {
                    $form = $event->getForm();
                    $Ad = $form->getData();
                    if ($app['eccube.plugin.ad_manage.repository.ad']->checkCodeDuplication($Ad)) {
                        $form['code']->addError(new FormError('form.type.ad_code.duplicated'));
                    }
                }
            );
    }
}