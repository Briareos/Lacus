<?php

namespace Lacus\MainBundle\Content\Segment;

use Lacus\MainBundle\Content\Segment\AbstractSegment;

class Tags extends AbstractSegment
{
    public function getType()
    {
        return 'tags';
    }

    public function getValue(array $options = array())
    {
        if (isset($options['glue'])) {
            return implode($options['glue'], $this->value);
        }
        return parent::getValue($options);
    }

}