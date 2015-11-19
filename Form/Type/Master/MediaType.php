<?php

namespace Plugin\AdManage\Form\Type\Master;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'class' => 'Plugin\AdManage\Entity\Master\Media',
                'query_builder' => function (EntityRepository $er) {
                    return $er
                        ->createQueryBuilder('m')
                        ->orderBy('m.rank', 'ASC');
                },
                'property' => 'name',
                'label' => '媒体グループ',
                'multiple' => false,
                'expanded' => false,
                'required' => true,
                'empty_value' => false,
                'empty_data' => null,
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'media';
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return 'entity';
    }
}