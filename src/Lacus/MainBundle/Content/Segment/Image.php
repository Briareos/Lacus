<?php

namespace Lacus\MainBundle\Content\Segment;

use Lacus\MainBundle\Content\Segment\File;

class Image extends File
{
    public function getType()
    {
        return 'image';
    }

    public function getDefaultOptions()
    {
        return array(
            'width' => null,
            'height' => null,
        );
    }
}