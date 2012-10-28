<?php

namespace Lacus\MainBundle\Post;

trait FieldNameConverterTrait
{
    public function getSafeFieldName($fieldName)
    {
        return str_replace(array('[', ']'), array('_-:', ':-_'), $fieldName);
    }

    public function getRawFieldName($fieldName)
    {
        return str_replace(array('_-:', ':-_'), array('[', ']'), $fieldName);
    }
}