<?php

namespace Lacus\MainBundle\Content\Segment;

use Lacus\MainBundle\Content\Segment\AbstractSegment;

class Text extends AbstractSegment
{
    public function getType()
    {
        return 'text';
    }

}