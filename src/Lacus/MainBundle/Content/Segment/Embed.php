<?php

namespace Lacus\MainBundle\Content\Segment;

use Lacus\MainBundle\Content\Segment\AbstractSegment;

class Embed extends AbstractSegment
{
    public function getType()
    {
        return 'embed';
    }

}