<?php

namespace Lacus\MainBundle\Content\Segment;

class Url extends Text
{
    public function getValue(array $options = array())
    {
        if (isset($options['query'])) {
            $url = $this->value;
            $hasQuery = (strpos($url, '?') !== false);

            if ($hasQuery) {
                $url .= '&' . http_build_query($options['query']);
            } else {
                $url .= '?' . http_build_query($options['query']);
            }
            return $url;
        }
        return parent::getValue($options);
    }

    public function getType()
    {
        return 'url';
    }
}