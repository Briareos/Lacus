<?php

namespace Lacus\MainBundle\Content\Category;

use Lacus\MainBundle\Content\Category\Category;

class CategoryContainer implements \IteratorAggregate
{
    private $categories = array();

    public function add(Category $category)
    {
        $this->categories[$category->getId()] = $category;
        return $this;
    }

    public function all()
    {
        return $this->categories;
    }

    /**
     * @param $name
     * @return Category
     */
    public function get($name)
    {
        return $this->categories[$name];
    }

    public function has($name)
    {
        return isset($this->categories[$name]);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->categories);
    }
}