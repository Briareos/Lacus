<?php

namespace Lacus\MainBundle\Content\Sortable;

class SortableField
{
    private $name;

    private $id = false;

    private $parent;

    private $children = array();

    public function __construct($name, $id = false, array $children = null)
    {
        $this->setName($name);
        $this->setId($id);
        $this->setChildren($children);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return SortableField
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(SortableField $parent)
    {
        $this->parent = $parent;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function setChildren(array $children = null)
    {
        if ($children !== null) {
            foreach ($children as $child) {
                if (!$child instanceof SortableField) {
                    throw new \InvalidArgumentException('Field children must be instances of Field.');
                }
                $child->setParent($this);
                $this->children[] = $child;
            }
        }
    }

    public function getPath()
    {
        $path = array($this->getName());
        $current = $this;
        while ($parent = $current->getParent()) {
            $path[] = $parent->getName();
            $current = $parent;
        }
        return implode('-', array_reverse($path));
    }

    public function getDepth()
    {
        $depth = 0;
        $current = $this;
        while ($parent = $current->getParent()) {
            $depth++;
            $current = $parent;
        }
        return $depth;
    }
}