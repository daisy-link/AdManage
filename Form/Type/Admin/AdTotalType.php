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

class AdTotalType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_ad_total';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('total_date_from', 'date', array(
                'label' => '開始日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('total_date_to', 'date', array(
                'label' => '終了',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('order_status', 'order_status', array(
                'label' => '対応状況',
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
        
    }
}