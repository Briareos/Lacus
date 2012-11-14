<?php

namespace Lacus\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MapperFileType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mapper_file';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Lacus\MainBundle\Entity\File',
                'file_type' => 'file',
                'show_alternatives' => false,
                'alternatives' => array(),
            )
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['alternatives'] = ($options['show_alternatives'] && count($options['alternatives']) ? $options['alternatives'] : array());
        $view->vars['file_type'] = $options['file_type'];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('remotePath', 'url');
        $builder->add('file', 'file');
    }

}