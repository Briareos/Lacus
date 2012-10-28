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
        $resolver->setDefaults(array(
            'data_class' => 'Lacus\MainBundle\Entity\File',
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('remotePath', 'url');
        $builder->add('file', 'file');
    }
}