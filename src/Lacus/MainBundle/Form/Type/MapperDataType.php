<?php

namespace Lacus\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class MapperDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mapper_data';
    }

    public function getParent()
    {
        return 'textarea';
    }
}