<?php

namespace Lacus\MainBundle\Content\Sortable;

use Lacus\MainBundle\Content\Sortable\SortableField;

class SortableFieldContainer implements \IteratorAggregate, \Countable
{
    private $sortable = array();

    public function add(SortableField $field)
    {
        $this->sortable[$field->getPath()] = $field;
        foreach ($field->getChildren() as $child) {
            /** @var $child SortableField */
            $this->add($child);
        }
        return $this;
    }

    public function get($name)
    {
        return $this->sortable[$name];
    }

    public function has($name)
    {
        return isset($this->sortable[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->sortable);
    }

    public function count()
    {
        return count($this->sortable);
    }


}