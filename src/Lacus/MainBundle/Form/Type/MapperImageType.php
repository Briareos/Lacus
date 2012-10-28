<?php

namespace Lacus\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MapperImageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mapper_image';
    }

    public function getParent()
    {
        return 'text';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'show_alternatives' => false,
            'alternatives' => array(),
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['alternatives'] = ($options['show_alternatives'] && count($options['alternatives']) ? $options['alternatives'] : array());
    }


}