<?php

namespace Lacus\MainBundle\Content\Segment;

use Lacus\MainBundle\Content\Segment\AbstractSegment;

class File extends AbstractSegment
{
    public function getType()
    {
        return 'file';
    }

}