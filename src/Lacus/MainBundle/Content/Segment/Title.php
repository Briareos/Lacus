<?php

namespace Lacus\MainBundle\Content\Segment;

use Lacus\MainBundle\Content\Segment\AbstractSegment;

class Title extends AbstractSegment
{
    public function getType()
    {
        return 'title';
    }

}