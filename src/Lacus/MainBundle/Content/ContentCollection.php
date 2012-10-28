<?php

namespace Lacus\MainBundle\Content;

use Lacus\MainBundle\Content\Content;

class ContentCollection implements \IteratorAggregate
{
    private $content = array();

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->content);
    }

    public function getContent()
    {
        return $this->content;
    }

    public function addContent(Content $content)
    {
        $this->content[] = $content;
    }

    public function hasContent()
    {
        return count($this->content) > 0;
    }

    /**
     * @return array
     */
    public function getContentUuids()
    {
        $ids = array();
        foreach($this->content as $content){
            /** @var $content Content */
            $ids[] = $content->getUuid();
        }
        return $ids;
    }
}