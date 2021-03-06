<?php

namespace Lacus\MainBundle\Content\Segment;

use Lacus\MainBundle\Content\Segment\AbstractSegment;

class File extends AbstractSegment
{
    private $alternatives = array();

    public function addAlternative($alternative)
    {
        $this->alternatives[] = $alternative;
    }

    public function getAlternatives()
    {
        return $this->alternatives;
    }

    public function setAlternatives($alternatives)
    {
        $this->alternatives = $alternatives;
    }

    public function hasAlternatives()
    {
        return count($this->alternatives) > 0;
    }

    public function getType()
    {
        return 'file';
    }
}