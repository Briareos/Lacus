<?php

namespace Lacus\MainBundle\Content\Segment;

use Lacus\MainBundle\Content\Segment\AbstractSegment;

class Article extends AbstractSegment
{
    public function getType()
    {
        return 'article';
    }

}