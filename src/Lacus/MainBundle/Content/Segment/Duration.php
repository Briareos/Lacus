<?php

namespace Lacus\MainBundle\Content\Segment;

use Lacus\MainBundle\Content\Segment\AbstractSegment;

class Duration extends AbstractSegment
{
    public function getType()
    {
        return 'duration';
    }

}