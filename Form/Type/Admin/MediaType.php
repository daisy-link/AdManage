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

class MediaType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_media';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Plugin\AdManage\Entity\Master\Media',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                array(
                    'label' => '媒体グループ名',
                    'constraints' => array(
                        new Assert\NotBlank(),
                    ),
                )
            )
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
    }
}