<?php

namespace Lacus\MainBundle\Content\Segment;

use Lacus\MainBundle\Content\Segment\AbstractSegment;

class ImageCollection extends Image
{
    public function getType()
    {
        return 'image_collection';
    }

    public function getValue(array $options = array())
    {
        if (isset($options['glue'])) {
            return implode($options['glue'], $this->value);
        }
        return parent::getValue($options);
    }
}