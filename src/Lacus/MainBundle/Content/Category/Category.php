<?php

namespace Lacus\MainBundle\Content\Category;

class Category
{
    private $name;

    private $id;

    function __construct($name, $id)
    {
        $this->name = $name;
        $this->id = $id;
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
}