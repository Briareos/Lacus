<?php

namespace Lacus\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

class MapperConfigureFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mapper_configure';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden', array(
            'disabled' => true,
        ));
        $builder->add('account', 'entity', array(
            'class'=>'Lacus\MainBundle\Entity\Account',
            'property'=>'username',
            'required' => false,
            'mapped' => false,
        ));
        $builder->add('data', 'mapper_data', array(
            'required' => true,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Lacus\MainBundle\Entity\Mapper',
        ));
    }
}