<?php

namespace Lacus\MainBundle\Content\Segment;

use Lacus\MainBundle\Content\Segment\AbstractSegment;

class Article extends AbstractSegment
{
    public function getType()
    {
        return 'article';
    }

    public function getValue(array $options = array())
    {
        return $this->value;
    }

    public function getDefaultOptions()
    {
        return array(
            'paragraph_to_break' => false,
        );
    }
}